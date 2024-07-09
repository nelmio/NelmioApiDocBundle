// This file is part of the API Platform project.
//
// (c) Kévin Dunglas <dunglas@gmail.com>
//
// For the full copyright and license information, please view the LICENSE
// file that was distributed with this source code.

function loadSwaggerUI(userOptions = {}) {
  const data = JSON.parse(document.getElementById('swagger-data').innerText);
  const defaultOptions = {
    spec: data.spec,
    dom_id: '#swagger-ui',
    validatorUrl: null,
    presets: [
      SwaggerUIBundle.presets.apis,
      SwaggerUIStandalonePreset
    ],
    plugins: [
      SwaggerUIBundle.plugins.DownloadUrl
    ],
    layout: 'StandaloneLayout'
  };
  const options = Object.assign({}, defaultOptions, userOptions);
  const ui = SwaggerUIBundle(options);

  const storageKey = 'nelmio_api_auth';

  function getAuthorizationsFromStorage() {
    if (sessionStorage.getItem(storageKey)) {
      try {
        return JSON.parse(sessionStorage.getItem(storageKey));
      } catch (ignored) {
        // catch any errors here so it does not stop script execution
      }
    }

    return {};
  }

  // if we have auth in storage use it
  try {
    const currentAuthorizations = getAuthorizationsFromStorage();
    Object.keys(currentAuthorizations).forEach(k => ui.authActions.authorize({[k]: currentAuthorizations[k]}));
  } catch (ignored) {
    // catch any errors here so it does not stop script execution
  }

  // hook into authorize to store the auth in local storage when user performs authorization
  const currentAuthorize = ui.authActions.authorize;
  ui.authActions.authorize = function (payload) {
    try {
      sessionStorage.setItem(storageKey, JSON.stringify(Object.assign(
        getAuthorizationsFromStorage(),
        payload
      )));
    } catch (ignored) {
      // catch any errors here so it does not stop script execution
    }

    return currentAuthorize(payload);
  };

  // hook into logout to clear auth from storage if user logs out
  const currentLogout = ui.authActions.logout;
  ui.authActions.logout = function (payload) {
    try {
      let currentAuth = getAuthorizationsFromStorage();
      payload.forEach(k => delete currentAuth[k]);
      sessionStorage.setItem(storageKey, JSON.stringify(currentAuth));
    } catch (ignored) {
      // catch any errors here so it does not stop script execution
    }

    return currentLogout(payload);
  };

  window.ui = ui;
}
