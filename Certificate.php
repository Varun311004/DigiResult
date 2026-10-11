<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/student-queries.php';

require_student();


/*
|--------------------------------------------------------------------------
| STUDENT
|--------------------------------------------------------------------------
*/

$student = get_student(
    (int) $_SESSION['student_id']
);

if (!$student) {

    logout_all();

    flash(
        'error',
        'Your student record is no longer available. Please contact the institute.'
    );

    redirect('index.php');
}


/*
|--------------------------------------------------------------------------
| PUBLICATION
|--------------------------------------------------------------------------
*/

$publicationId = safe_int(
    $_GET['publication'] ?? null
);

$publication = get_publication_for_student(
    (int) $student['id'],
    $publicationId
);

if (!$publication) {

    flash(
        'error',
        'That published result could not be found.'
    );

    redirect('student/dashboard.php');
}


/*
|--------------------------------------------------------------------------
| ACADEMIC CONTEXT
|--------------------------------------------------------------------------
*/

$year = (string) $publication['year'];
$batch = (string) $publication['batch'];
$semester = (string) $publication['semester'];
$department = (string) $publication['department'];


/*
|--------------------------------------------------------------------------
| GET ALL SUBJECTS
|--------------------------------------------------------------------------
|
| This returns:
|
|   configured subjects
|   +
|   legacy/result subjects
|
| and attaches this student's marks where available.
|--------------------------------------------------------------------------
*/

$rows = get_certificate_rows_for_student(
    (string) $student['ien_number'],
    $year,
    $batch,
    $semester,
    $department
);


/*
|--------------------------------------------------------------------------
| CALCULATE TOTALS
|--------------------------------------------------------------------------
|
| These are raw marks totals from the data currently available.
|
| Official:
|   credits
|   grade points
|   SGPI
|   CGPI
|
| are deliberately NOT invented.
|--------------------------------------------------------------------------
*/

$grandTotal = 0;

foreach ($rows as $row) {

    $grandTotal +=
        (int) ($row['TW_marks'] ?? 0)
        +
        (int) ($row['PR_marks'] ?? 0)
        +
        (int) ($row['ESE_marks'] ?? 0);
}


/*
|--------------------------------------------------------------------------
| STUDENT DATA
|--------------------------------------------------------------------------
*/

$studentName = trim(
    (string) $student['first_name']
    . ' '
    . (string) $student['last_name']
);

$seatNumber = (string) (
    $student['student_number'] ?? ''
);

$ienNumber = (string) (
    $student['ien_number'] ?? ''
);


/*
|--------------------------------------------------------------------------
| TABLE DIMENSIONS
|--------------------------------------------------------------------------
|
| The selected template is 1086 × 1448.
|
| Printed table:
|
| x = 34
| right = 1054
| header bottom = 513
| main table bottom = 970
|
| Therefore the dynamic rows must occupy ONLY:
|
|   y = 513 → 970
|
| The template itself already contains:
|
|   - table header
|   - Total row
|   - Remark row
|
| We only draw the subject rows.
|--------------------------------------------------------------------------
*/

$subjectCount = count($rows);


/*
|--------------------------------------------------------------------------
| Dynamic font sizing
|--------------------------------------------------------------------------
*/

if ($subjectCount >= 16) {
    $subjectRowsClass = 'certificate-rows-16-plus';
} elseif ($subjectCount >= 13) {
    $subjectRowsClass = 'certificate-rows-13-15';
} elseif ($subjectCount >= 10) {
    $subjectRowsClass = 'certificate-rows-10-12';
} elseif ($subjectCount >= 7) {
    $subjectRowsClass = 'certificate-rows-7-9';
} else {
    $subjectRowsClass = 'certificate-rows-1-6';
}


/*
|--------------------------------------------------------------------------
| PAGE
|--------------------------------------------------------------------------
*/

page_start(
    'Certificate / Grade Card',
    'student',
    '',
    ['assets/css/certificate.css']
);

student_nav();

render_flashes();

?>
<main class="student-shell certificate-page">


    <!-- ==========================================================
         ACTION BUTTONS
    =========================================================== -->

    <div class="certificate-actions certificate-actions-screen">

        <a
            class="btn btn-secondary"
            href="<?= e(
                dr_url(
                    'student/result.php?publication='
                    . (int) $publication['id']
                )
            ) ?>"
        >
            Back to result
        </a>

        <button
            class="btn btn-primary"
            type="button"
            onclick="window.print()"
        >
            Print / Save PDF
        </button>

    </div>


    <!-- ==========================================================
         CERTIFICATE SHEET
    =========================================================== -->

    <section
        class="grade-card"
        aria-label="DigiResult Grade Card"
    >


        <!-- ======================================================
             STATIC TEMPLATE IMAGE
        ======================================================= -->

        <img
            class="grade-card-template"
            src="<?= e(
                dr_url(
                    'assets/images/certificate.png'
                )
            ) ?>"
            alt="New Horizon Institute Grade Card"
        >


        <!-- ======================================================
             DYNAMIC OVERLAY
        ======================================================= -->

        <div class="grade-card-layer">


            <!-- ==================================================
                 STUDENT DETAILS
            ================================================== -->

            <div class="dynamic-student-details">

                <!-- NAME -->
                <div class="student-value student-name">
                    <?= e($studentName) ?>
                </div>


                <!-- SEAT NUMBER -->
                <div class="student-value student-seat">
                    <?= e($seatNumber) ?>
                </div>


                <!-- SEM -->
                <div class="student-value student-sem">
                    <?= e($semester) ?>
                </div>


                <!-- YEAR -->
                <div class="student-value student-year">
                    <?= e($year) ?>
                </div>


                <!-- IEN -->
                <div class="student-value student-ien">
                    <?= e($ienNumber) ?>
                </div>


                <!-- BRANCH -->
                <div class="student-value student-branch">
                    <?= e($department) ?>
                </div>


                <!-- EXAMINATION
                     UNKNOWN FOR NOW → intentionally blank
                -->
                <div class="student-value student-examination"></div>


                <!-- SERIAL NUMBER
                     UNKNOWN FOR NOW → intentionally blank
                -->
                <div class="student-value student-serial"></div>

            </div>


            <!-- ==================================================
                 DYNAMIC SUBJECT BODY
            ================================================== -->

            <?php if ($subjectCount > 0): ?>

                <div
                    class="dynamic-subject-body <?= e($subjectRowsClass) ?>"
                >

                    <?php foreach (
                        $rows as $index => $row
                    ): ?>

                        <?php

                        $tw = $row['TW_marks'];
                        $pr = $row['PR_marks'];
                        $ese = $row['ESE_marks'];

                        $overall =
                            (int) ($tw ?? 0)
                            +
                            (int) ($pr ?? 0)
                            +
                            (int) ($ese ?? 0);

                        ?>

                        <div class="subject-row">


                            <!-- =================================
                                 COURSE CODE
                                 UNKNOWN → BLANK
                            ================================== -->

                            <div class="subject-cell course-code-cell"></div>


                            <!-- =================================
                                 COURSE TITLE
                            ================================== -->

                            <div
                                class="subject-cell course-title-cell"
                                title="<?= e(
                                    str_replace(
                                        '_',
                                        ' ',
                                        (string) $row['subject']
                                    )
                                ) ?>"
                            >
                                <?= e(
                                    str_replace(
                                        '_',
                                        ' ',
                                        (string) $row['subject']
                                    )
                                ) ?>
                            </div>


                            <!-- =================================
                                 COURSE CREDITS
                                 UNKNOWN → BLANK
                            ================================== -->

                            <div class="subject-cell course-credit-cell"></div>


                            <!-- =================================
                                 ESE / PR-OR

                                 We have two known values:
                                   ESE
                                   PR

                                 The template has these stacked
                                 under the same grade group.
                            ================================== -->

                            <div class="subject-cell ese-pr-cell">

                                <div class="mark-stack">

                                    <span>
                                        <?= $ese === null
                                            ? ''
                                            : e((string) $ese)
                                        ?>
                                    </span>

                                    <span>
                                        <?= $pr === null
                                            ? ''
                                            : e((string) $pr)
                                        ?>
                                    </span>

                                </div>

                            </div>


                            <!-- =================================
                                 IA / TW

                                 IA is unknown.
                                 TW is known.
                            ================================== -->

                            <div class="subject-cell ia-tw-cell">

                                <?php if ($tw !== null): ?>

                                    <?= e(
                                        (string) $tw
                                    ) ?>

                                <?php endif; ?>

                            </div>


                            <!-- =================================
                                 OVERALL
                            ================================== -->

                            <div class="subject-cell overall-cell">

                                <?php

                                $hasAnyMark =
                                    $tw !== null
                                    ||
                                    $pr !== null
                                    ||
                                    $ese !== null;

                                ?>

                                <?php if ($hasAnyMark): ?>

                                    <strong>
                                        <?= e(
                                            (string) $overall
                                        ) ?>
                                    </strong>

                                <?php endif; ?>

                            </div>


                            <!-- =================================
                                 CREDIT EARNED
                                 UNKNOWN
                            ================================== -->

                            <div class="subject-cell credit-earned-cell"></div>


                            <!-- =================================
                                 GRADE POINT
                                 UNKNOWN
                            ================================== -->

                            <div class="subject-cell grade-point-cell"></div>


                            <!-- =================================
                                 C × G
                                 UNKNOWN
                            ================================== -->

                            <div class="subject-cell cxg-cell"></div>

                        </div>

                    <?php endforeach; ?>

                </div>


                <!-- =================================================
                     RAW MARK TOTAL

                     Only the overall raw-mark total is currently
                     known.
                ================================================== -->

                <div class="dynamic-total-value">

                    <?= e(
                        (string) $grandTotal
                    ) ?>

                </div>

            <?php else: ?>

                <div class="dynamic-no-subjects">
                    No subjects are configured for this academic context.
                </div>

            <?php endif; ?>


            <!-- ==================================================
                 FUTURE DATABASE FIELDS
            ================================================== -->

            <!--
                Remark  → blank
                SGPI    → blank
                CGPI    → blank
                Exam    → blank
                Serial  → blank
                Date    → blank
                Course code → blank
                Credits → blank
                Credit earned → blank
                Grade point → blank
                C × G → blank

                These should be populated later when those fields
                are added to the database.
            -->

        </div>

    </section>

</main>





<?php page_end(); ?>
