'use strict';

function loadRedocly(userOptions = {}) {
    const data = JSON.parse(document.getElementById('swagger-data').innerText);

    Redoc.init(data.spec, userOptions, document.getElementById('swagger-ui'));
}
