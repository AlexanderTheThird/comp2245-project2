//naaryah naraine common.js
// get da CSRF token from the page
function csrfToken() {
  const meta = document.querySelector('meta[name="csrf-token"]');

  return meta ? meta.content : '';
}
// this sends requests to the API
async function apiRequest(url, options = {}) {

  const method = options.method || 'GET';
  const headers = {
    'Accept': 'application/json'
  };
  // add the CSRF token to requests that change data
  if (method !== 'GET') {
    headers['X-CSRF-Token'] = csrfToken();
  }
  if (options.body !== undefined) {
    headers['Content-Type'] = 'application/json';
  }
  const response = await fetch(url, {
    method: method,
    headers: headers,
    credentials: 'same-origin',
    body: options.body !== undefined
      ? JSON.stringify(options.body)
      : undefined
  });
  const data = await response.json();

  // this is to hndle errors returned by the server.
  if (!response.ok) {
    throw new Error(data.error || 'Something went wrong.');
  }
  return data;
}