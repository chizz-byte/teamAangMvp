// Small shared helpers.
function csrfHeaders() {
  const meta = document.querySelector('meta[name="csrf-token"]');
  return meta ? { 'X-CSRF-Token': meta.content } : {};
}
