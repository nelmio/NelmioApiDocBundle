// This file is part of the API Platform project.
//
// (c) Kévin Dunglas <dunglas@gmail.com>
//
// For the full copyright and license information, please view the LICENSE
// file that was distributed with this source code.

window.onload = () => {
  const data = JSON.parse(document.getElementById('swagger-data').innerText);
  const ui = SwaggerUIBundle({
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
  });

  const storageKey = 'nelmio_api_auth';

  // if we have auth in storage use it
  if (sessionStorage.getItem(storageKey)) {
    try {
      ui.authActions.authorize(JSON.parse(sessionStorage.getItem(storageKey)));
    } catch (ignored) {
      // catch any errors here so it does not stop script execution
    }
  }

  // hook into authorize to store the auth in local storage when user performs authorization
  const currentAuthorize = ui.authActions.authorize;
  ui.authActions.authorize = function (payload) {
    sessionStorage.setItem(storageKey, JSON.stringify(payload));
    return currentAuthorize(payload);
  };

  // hook into logout to clear auth from storage if user logs out
  const currentLogout = ui.authActions.logout;
  ui.authActions.logout = function (payload) {
    sessionStorage.removeItem(storageKey);
    return currentLogout(payload);
  };

  window.ui = ui;
};
