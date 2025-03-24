<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Documentation</title>
    <style>
        /* Base Styles */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9fafb;
            color: #333;
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        h1 {
            font-size: 2.5rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 20px;
            text-align: center;
        }

        p {
            font-size: 1.1rem;
            color: #555;
            text-align: center;
            margin-bottom: 40px;
        }

        /* Resource List */
        .resource-list {
            list-style-type: none;
            padding: 0;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .resource-list li {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .resource-list li:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .resource-list a {
            text-decoration: none;
            color: #007BFF;
            font-size: 1.2rem;
            font-weight: 600;
            display: block;
            margin-bottom: 10px;
        }

        .resource-list a:hover {
            color: #0056b3;
        }

        .resource-list p {
            font-size: 1rem;
            color: #666;
            margin: 0;
        }

        /* Footer */
        footer {
            text-align: center;
            margin-top: 40px;
            padding: 20px;
            background-color: #2c3e50;
            color: #fff;
            border-radius: 10px;
        }

        footer a {
            color: #007BFF;
            text-decoration: none;
        }

        footer a:hover {
            text-decoration: underline;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            h1 {
                font-size: 2rem;
            }

            .resource-list {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>API Documentation</h1>
        <p>Welcome to the API documentation. Below is a list of available resources:</p>

        <ul class="resource-list">
            <li>
                <a href="{{ url('/api-docs/item-units') }}">Item Units</a>
                <p>Manage item units with endpoints for listing, creating, updating, and deleting records.</p>
            </li>
            <li>
                <a href="{{ url('/api-docs/item-categories') }}">Item Categories</a>
                <p>Manage item categories with endpoints for listing, creating, updating, and deleting records.</p>
            </li>
            <li>
                <a href="{{ url('/api-docs/item-classifications') }}">Item Classifications</a>
                <p>Manage log descriptions with endpoints for listing, creating, updating, and deleting records.</p>
            </li>
            <li>
                <a href="{{ url('/api-docs/success-indicators') }}">Success Indicators</a>
                <p>Manage log descriptions with endpoints for listing, creating, updating, and deleting records.</p>
            </li>
            <li>
                <a href="{{ url('/api-docs/type-of-functions') }}">Type Of Functions</a>
                <p>Manage log descriptions with endpoints for listing, creating, updating, and deleting records.</p>
            </li>
            <li>
                <a href="{{ url('/api-docs/purchase-types') }}">Purchase Type</a>
                <p>Manage log descriptions with endpoints for listing, creating, updating, and deleting records.</p>
            </li>
            <li>
                <a href="{{ url('/api-docs/objectives') }}">Objectives</a>
                <p>Manage log descriptions with endpoints for listing, creating, updating, and deleting records.</p>
            </li>
            <li>
                <a href="{{ url('/api-docs/log-descriptions') }}">Log Descriptions</a>
                <p>Manage log descriptions with endpoints for listing, creating, updating, and deleting records.</p>
            </li>
        </ul>

        <footer>
            <p>© 2023 Zamboanga City Medical. All rights reserved. | <a href="#">Privacy Policy</a></p>
        </footer>
    </div>
</body>
</html>