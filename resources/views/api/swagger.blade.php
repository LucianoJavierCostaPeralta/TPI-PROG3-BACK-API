<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $config->get('ui.title') ?? config('app.name').' - API Docs' }}</title>
    <link rel="stylesheet" href="{{ $config->renderer()->get('cdn') }}/swagger-ui.css">
    <style>
        html {
            box-sizing: border-box;
            overflow-y: scroll;
        }

        *, *::before, *::after {
            box-sizing: inherit;
        }

        body {
            margin: 0;
            background: #fafafa;
        }
    </style>
</head>
<body>
<div id="swagger-ui"></div>

<script src="{{ $config->renderer()->get('cdn') }}/swagger-ui-bundle.js"></script>
<script src="{{ $config->renderer()->get('cdn') }}/swagger-ui-standalone-preset.js"></script>
<script>
    window.addEventListener('load', () => {
        SwaggerUIBundle({
            spec: @json($spec),
            dom_id: '#swagger-ui',
            deepLinking: true,
            presets: [
                SwaggerUIBundle.presets.apis,
                SwaggerUIStandalonePreset,
            ],
            plugins: [SwaggerUIBundle.plugins.DownloadUrl],
            layout: 'StandaloneLayout',
            persistAuthorization: @json($config->renderer()->get('persistAuthorization', true)),
            displayRequestDuration: @json($config->renderer()->get('displayRequestDuration', true)),
            tryItOutEnabled: @json($config->renderer()->get('tryItOutEnabled', true)),
            requestInterceptor: (request) => {
                request.credentials = 'same-origin';
                return request;
            },
        });
    });
</script>
</body>
</html>
