document.addEventListener('click', event => {
  const button = event.target.closest('[data-preview-action]');
  if (!button) return;
  const form = button.closest('form');
  if (!form.reportValidity()) return;
  let feedback = form.querySelector('[data-preview-feedback]');
  if (!feedback) {
    feedback = document.createElement('p');
    feedback.className = 'preview-note span-2';
    feedback.dataset.previewFeedback = '';
    feedback.setAttribute('role','status');
    form.append(feedback);
  }
  feedback.innerHTML = 'This is a static form preview; nothing was sent or saved. <a href="demo.html">Watch the working backend →</a>';
});
document.addEventListener('submit', event => {
  if (event.target.matches('[data-preview-form]')) event.preventDefault();
});
