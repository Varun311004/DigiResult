document.addEventListener("DOMContentLoaded", function () {
    const profilePic = document.querySelector(".navbar-profile .profile-pic");
    const profilePopup = document.querySelector(
        ".navbar-profile .profile-popup"
    );
    const yearButtons = document.querySelectorAll(".box:nth-child(1) button");
    const seasonButtons = document.querySelectorAll(".box:nth-child(2) button");
    const departmentButtons = document.querySelectorAll(
        ".box:nth-child(3) button"
    );

    // Toggle popup on profile picture click
    profilePic.addEventListener("click", function (e) {
        profilePopup.classList.toggle("show");
        if (profilePopup.classList.contains("show")) {
            profilePopup.style.display = "block"; // Make sure it's displayed
        } else {
            setTimeout(() => {
                profilePopup.style.display = "none"; // Hide after animation
            }, 300); // Match the duration of the transition
        }
    });

    // Hide popup when clicking outside of the profile picture and popup
    document.addEventListener("click", function (e) {
        if (
            !profilePic.contains(e.target) &&
            !profilePopup.contains(e.target)
        ) {
            profilePopup.classList.remove("show");
            setTimeout(() => {
                profilePopup.style.display = "none";
            }, 300); // Match the duration of the transition
        }
    });

    function handleButtonGroupSelection(buttonGroup) {
        buttonGroup.forEach((button) => {
            button.addEventListener("click", function () {
                // Remove the 'selected' class from all buttons in this group
                buttonGroup.forEach((btn) => btn.classList.remove("selected"));

                // Add the 'selected' class to the clicked button
                this.classList.add("selected");
            });
        });
    }

    handleButtonGroupSelection(yearButtons);
    handleButtonGroupSelection(seasonButtons);
    handleButtonGroupSelection(departmentButtons);
});
