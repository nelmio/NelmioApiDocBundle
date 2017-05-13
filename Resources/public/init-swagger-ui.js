// This file is part of the API Platform project.
//
// (c) Kévin Dunglas <dunglas@gmail.com>
//
// For the full copyright and license information, please view the LICENSE
// file that was distributed with this source code.

$(function () {
    var data = JSON.parse($('#swagger-data').html());
    window.swaggerUi = new SwaggerUi({
        url: '/',
        spec: data.spec,
        dom_id: 'swagger-ui-container',
        supportedSubmitMethods: ['get', 'post', 'put', 'delete'],
        onComplete: function() {
            $('pre code').each(function(i, e) {
                hljs.highlightBlock(e)
            });
        },
        onFailure: function() {
            log('Unable to Load SwaggerUI');
        },
        docExpansion: 'list',
        jsonEditor: false,
        defaultModelRendering: 'schema',
        showRequestHeaders: true
    });

    window.swaggerUi.load();

    function log() {
        if ('console' in window) {
            console.log.apply(console, arguments);
        }
    }
});
