document.addEventListener('click', (event) => {
  const close = event.target.closest('.flash-close');
  if (close) close.closest('.flash')?.remove();
  const confirmEl = event.target.closest('[data-confirm]');
  if (confirmEl && !window.confirm(confirmEl.dataset.confirm)) event.preventDefault();
});
document.querySelectorAll('[data-autohide]').forEach((el) => {
  window.setTimeout(() => el.remove(), Number(el.dataset.autohide || 5000));
});
document.querySelectorAll('input[data-upper]').forEach((input) => {
  input.addEventListener('input', () => { input.value = input.value.toUpperCase(); });
});


(() => {
  const button = document.getElementById('resendOtpButton');
  const note = document.getElementById('resendOtpNote');
  if (!button || !note) return;
  const raw = Number(note.dataset.resendAt || 0);
  if (!raw) return;
  const tick = () => {
    const remaining = Math.max(0, raw - Math.floor(Date.now() / 1000));
    if (remaining > 0) {
      button.disabled = true;
      button.textContent = `Resend OTP (${remaining})`;
      note.textContent = 'Please wait before requesting another code.';
      window.setTimeout(tick, 1000);
    } else {
      button.disabled = false;
      button.textContent = 'Resend OTP';
      note.textContent = 'You can request another code now.';
    }
  };
  tick();
})();
