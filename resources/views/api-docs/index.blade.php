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

        p.intro {
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
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .resource-list li:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .resource-link {
            display: block;
            padding: 20px;
            text-decoration: none;
            color: inherit;
            height: 100%;
        }

        .resource-link h2 {
            color: #007BFF;
            font-size: 1.2rem;
            font-weight: 600;
            margin-top: 0;
            margin-bottom: 15px;
        }

        .resource-link p {
            font-size: 1rem;
            color: #666;
            margin: 0;
            text-align: left;
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
        <p class="intro">Welcome to the API documentation. Below is a list of available resources</p>

        <ul class="resource-list">
            <li>
                <a href="{{ url('/api-docs/item-units') }}" class="resource-link">
                    <h2>Item Units</h2>
                    <p>Standard measurement units for inventory items (e.g., pieces, boxes, bottles). Manage units with CRUD endpoints.</p>
                </a>
            </li>
            <li>
                <a href="{{ url('/api-docs/item-categories') }}" class="resource-link">
                    <h2>Item Categories</h2>
                    <p>Classification groups for inventory items (e.g., medicines, equipment, supplies). Manage categories with CRUD endpoints.</p>
                </a>
            </li>
            <li>
                <a href="{{ url('/api-docs/item-classifications') }}" class="resource-link">
                    <h2>Item Classifications</h2>
                    <p>Detailed categorization system for inventory items (e.g., antibiotics, surgical equipment). Manage classifications with CRUD endpoints.</p>
                </a>
            </li>
            <li>
                <a href="{{ url('/api-docs/items') }}" class="resource-link">
                    <h2>Items</h2>
                    <p>Complete inventory management for all products/items. Includes endpoints for listing, creating, updating, and deleting item records.</p>
                </a>
            </li>
            <li>
                <a href="{{ url('/api-docs/success-indicators') }}" class="resource-link">
                    <h2>Success Indicators</h2>
                    <p>Metrics for evaluating annual planning effectiveness (e.g., stock availability rate, order fulfillment time). Used with objectives for planning.</p>
                </a>
            </li>
            <li>
                <a href="{{ url('/api-docs/type-of-functions') }}" class="resource-link">
                    <h2>Type Of Functions</h2>
                    <p>Functional classifications for planning purposes (e.g., clinical support, patient care, administration). Aligns with objectives.</p>
                </a>
            </li>
            <li>
                <a href="{{ url('/api-docs/purchase-types') }}" class="resource-link">
                    <h2>Purchase Types</h2>
                    <p>Classification of procurement methods (e.g., emergency purchase, regular purchase, bulk order). Relates to planning records.</p>
                </a>
            </li>
            <li>
                <a href="{{ url('/api-docs/objectives') }}" class="resource-link">
                    <h2>Objectives</h2>
                    <p>Purpose statements for annual planning/ordering (e.g., "Ensure adequate stock of critical medicines"). Used with success indicators.</p>
                </a>
            </li>
            <li>
                <a href="{{ url('/api-docs/log-descriptions') }}" class="resource-link">
                    <h2>Log Descriptions</h2>
                    <p>Standardized system activity logs (e.g., "New item created", "Category updated"). Library for consistent system logging.</p>
                </a>
            </li>
        </ul>

        <footer>
            <p>© 2023 Zamboanga City Medical. All rights reserved. | <a href="#">Privacy Policy</a></p>
        </footer>
    </div>
</body>
</html>