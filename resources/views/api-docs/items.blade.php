<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Documentation - Items</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
            max-width: 100%; /* Use full width */
            margin: 0;
            padding: 20px;
        }

        h1 {
            font-size: 2.5rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 20px;
            text-align: center;
        }

        h2 {
            font-size: 2rem;
            font-weight: 600;
            color: #2c3e50;
            margin-top: 0;
            margin-bottom: 15px;
        }

        h3 {
            font-size: 1.5rem;
            font-weight: 600;
            color: #2c3e50;
            margin-top: 0;
            margin-bottom: 10px;
        }

        h4 {
            font-size: 1.25rem;
            font-weight: 600;
            color: #2c3e50;
            margin-top: 0;
            margin-bottom: 10px;
        }

        p {
            font-size: 1.1rem;
            color: #555;
            margin-bottom: 20px;
        }

        pre {
            background: #2c3e50;
            color: #fff;
            padding: 15px;
            border-radius: 8px;
            overflow-x: auto;
            font-family: 'Courier New', Courier, monospace;
            margin-bottom: 20px;
            position: relative;
        }
        
        .copy-button {
            position: absolute;
            top: 8px;
            right: 8px;
            background: #007BFF;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 8px 12px;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.85rem;
            transition: background 0.3s ease;
            gap: 8px; /* Space between icon and text */
            height: 36px;
            white-space: nowrap;
        }

        .copy-button:hover {
            background: #0056b3;
        }

        .copy-button i {
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .copy-button span {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .copy-notification {
            display: none;
            position: fixed;
            top: 15px;
            right: 15px;
            background: #28a745;
            color: #fff;
            padding: 10px 15px;
            border-radius: 5px;
            font-size: 0.9rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            z-index: 1000;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        table th, table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        table th {
            background-color: #2c3e50;
            color: #fff;
            font-weight: 600;
        }

        table tr:hover {
            background-color: #f1f1f1;
        }

        ul {
            padding-left: 20px;
            margin-bottom: 20px;
        }

        ul li {
            margin-bottom: 10px;
        }

        .endpoint {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .endpoint:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #007BFF;
            text-decoration: none;
            font-weight: 600;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .resource-documentation {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .resource-description {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 30px;
            border-left: 4px solid #007bff;
        }

        .model-fields table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .model-fields th {
            background-color: #2c3e50;
            color: white;
            font-weight: 600;
            padding: 12px 15px;
        }

        .model-fields td {
            padding: 12px 15px;
            border-bottom: 1px solid #e0e0e0;
        }

        .model-fields tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .common-units {
            margin-bottom: 30px;
        }

        .unit-examples {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .unit-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-top: 3px solid #007bff;
        }

        .unit-card h3 {
            margin-top: 0;
            color: #2c3e50;
        }

        .usage-notes {
            background: #fff8e6;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #ffc107;
        }

        .usage-notes ul {
            padding-left: 20px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            h1 {
                font-size: 2rem;
            }

            h2 {
                font-size: 1.75rem;
            }

            h3 {
                font-size: 1.25rem;
            }

            h4 {
                font-size: 1.1rem;
            }

            pre {
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="{{ url('/api-docs') }}" class="back-link">← Back to API Documentation</a>
        <h1>API Documentation - Items</h1>

        <!--- Create brief description here what is the purpose of resource what are the fields -->
    
    <div class="resource-description">
        <h2>Items Resource</h2>
        <p>
            The Items resource represents inventory products in the system. Each item belongs to a specific 
            unit, category, and classification, with associated pricing information. Items are used for 
            inventory management, procurement planning, and order tracking.
        </p>
        
        <h3>Key Features</h3>
        <ul>
            <li>Track product details and specifications</li>
            <li>Manage inventory pricing and budgeting</li>
            <li>Organize items by categories and classifications</li>
            <li>Support for soft deletion of items</li>
        </ul>
    </div>

    <div class="model-fields">
        <h2>Item Model Fields</h2>
        <table>
            <thead>
                <tr>
                    <th>Field</th>
                    <th>Type</th>
                    <th>Description</th>
                    <th>Required</th>
                    <th>Example</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>id</td>
                    <td>integer</td>
                    <td>Auto-incremented primary key</td>
                    <td>Auto</td>
                    <td>1</td>
                </tr>
                <tr>
                    <td>item_unit_id</td>
                    <td>integer</td>
                    <td>Measurement unit reference</td>
                    <td>Yes</td>
                    <td>3 (for "Box")</td>
                </tr>
                <tr>
                    <td>item_category_id</td>
                    <td>integer</td>
                    <td>Primary category reference</td>
                    <td>Yes</td>
                    <td>5 (for "Pharmaceuticals")</td>
                </tr>
                <tr>
                    <td>item_classification_id</td>
                    <td>integer</td>
                    <td>Detailed classification reference</td>
                    <td>Yes</td>
                    <td>12 (for "Antibiotics")</td>
                </tr>
                <tr>
                    <td>name</td>
                    <td>string</td>
                    <td>Descriptive item name</td>
                    <td>Yes</td>
                    <td>"Amoxicillin 500mg Capsules"</td>
                </tr>
                <tr>
                    <td>estimated_budget</td>
                    <td>decimal</td>
                    <td>Current market price estimate</td>
                    <td>Yes</td>
                    <td>25.75</td>
                </tr>
                <tr>
                    <td>deleted_at</td>
                    <td>timestamp</td>
                    <td>Soft deletion marker</td>
                    <td>No</td>
                    <td>null</td>
                </tr>
                <tr>
                    <td>created_at</td>
                    <td>timestamp</td>
                    <td>Record creation timestamp</td>
                    <td>Auto</td>
                    <td>2023-06-15 10:00:00</td>
                </tr>
                <tr>
                    <td>updated_at</td>
                    <td>timestamp</td>
                    <td>Record update timestamp</td>
                    <td>Auto</td>
                    <td>2023-06-15 10:30:00</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="relationships">
        <h2>Relationships</h2>
        <ul>
            <li><strong>BelongsTo ItemUnit</strong> - Defines the measurement unit for the item</li>
            <li><strong>BelongsTo ItemCategory</strong> - Categorizes the item (e.g., Medical, Surgical)</li>
            <li><strong>BelongsTo ItemClassification</strong> - Further classifies the item (e.g., Diagnostic, Consumable)</li>
        </ul>
    </div>

        <!-- Index Endpoint -->
        <div class="endpoint">
            <h2>GET /api/items</h2>
            <p>Retrieve item categories with options for pagination, selection mode, or fetching a single record by ID.</p>

            <h3>Parameters</h3>
            <table>
                <thead>
                    <tr>
                        <th>Parameter</th>
                        <th>Type</th>
                        <th>Description</th>
                        <th>Required</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>page</td>
                        <td>integer</td>
                        <td>The current page number for pagination. Default is 1.</td>
                        <td>No</td>
                    </tr>
                    <tr>
                        <td>per_page</td>
                        <td>integer</td>
                        <td>The number of records to return per page.</td>
                        <td>No</td>
                    </tr>
                    <tr>
                        <td>mode</td>
                        <td>string</td>
                        <td>The mode of retrieval. Options: <code>pagination</code> (default) or <code>selection</code>.</td>
                        <td>No</td>
                    </tr>
                    <tr>
                        <td>search</td>
                        <td>string</td>
                        <td>A search term to filter item by name.</td>
                        <td>No</td>
                    </tr>
                    <tr>
                        <td>item_id</td>
                        <td>integer</td>
                        <td>The ID of a specific item to retrieve.</td>
                        <td>No</td>
                    </tr>
                </tbody>
            </table>

            <h3>Modes</h3>
            <p>The <code>mode</code> parameter determines the format of the response:</p>
            <ul>
                <li>
                    <strong>pagination</strong> (default): Returns paginated results with metadata for navigating between pages.
                </li>
                <li>
                    <strong>selection</strong>: Returns a flat list of item categories suitable for use in dropdowns or selection components.
                </li>
            </ul>

            <h4>Example Request for Pagination Mode</h4>
            <pre>
GET {{ env('SERVER_DOMAIN') }}/api/items?page=1&per_page=10
                <button class="copy-button" onclick="copyToClipboard('{{ env('SERVER_DOMAIN') }}/api/items?page=1&per_page=10')">
                    <i class="fas fa-copy"></i> Copy URL
                </button>
            </pre>

            <h4>Example Response for Pagination Mode</h4>
            <pre>
{
    "data": [
        {
            "id": 1,
            "name": "Stethoscope",
            "estimated_budget": 0,
            "unit": "pc",
            "category": "MED-EQ",
            "classification": "DIAG-INST",
            "item_unit": {
                "id": 1,
                "name": "Piece",
                "code": "pc",
                "description": "Individual countable items (e.g., stethoscopes, microscopes)",
                "deleted_at": null,
                "created_at": "2025-03-24T18:23:45.000000Z",
                "updated_at": "2025-03-24T18:23:45.000000Z"
            },
            "item_category": {
                "id": 1,
                "name": "Medical Equipment",
                "code": "MED-EQ",
                "description": "Durable medical devices used for diagnosis, monitoring or treatment (e.g., ventilators, ECG machines)",
                "deleted_at": null,
                "created_at": "2025-03-24T18:27:37.000000Z",
                "updated_at": "2025-03-24T18:27:37.000000Z"
            },
            "item_classification": {
                "id": 1,
                "item_category_id": 6,
                "name": "Diagnostic Instruments",
                "code": "DIAG-INST",
                "description": "Handheld or portable devices for physical examination (e.g., stethoscopes, otoscopes)",
                "deleted_at": null,
                "created_at": "2025-03-24T18:38:46.000000Z",
                "updated_at": "2025-03-24T18:38:46.000000Z"
            },
            "created_at": "2025-03-24T18:44:44.000000Z",
            "updated_at": "2025-03-24T18:44:44.000000Z"
        },
        {
            "id": 2,
            "name": "Stethoscope",
            "estimated_budget": 150,
            "unit": "pc",
            "category": "MED-EQ",
            "classification": "DIAG-INST",
            "item_unit": {
                "id": 1,
                "name": "Piece",
                "code": "pc",
                "description": "Individual countable items (e.g., stethoscopes, microscopes)",
                "deleted_at": null,
                "created_at": "2025-03-24T18:23:45.000000Z",
                "updated_at": "2025-03-24T18:23:45.000000Z"
            },
            "item_category": {
                "id": 1,
                "name": "Medical Equipment",
                "code": "MED-EQ",
                "description": "Durable medical devices used for diagnosis, monitoring or treatment (e.g., ventilators, ECG machines)",
                "deleted_at": null,
                "created_at": "2025-03-24T18:27:37.000000Z",
                "updated_at": "2025-03-24T18:27:37.000000Z"
            },
            "item_classification": {
                "id": 1,
                "item_category_id": 6,
                "name": "Diagnostic Instruments",
                "code": "DIAG-INST",
                "description": "Handheld or portable devices for physical examination (e.g., stethoscopes, otoscopes)",
                "deleted_at": null,
                "created_at": "2025-03-24T18:38:46.000000Z",
                "updated_at": "2025-03-24T18:38:46.000000Z"
            },
            "created_at": "2025-03-24T19:01:41.000000Z",
            "updated_at": "2025-03-24T19:01:41.000000Z"
        },
        ...
    ],
    "metadata": {
        "methods": "[GET, POST, PUT, DELETE]",
        "pagination": [
            {
                "title": "Prev",
                "link": null,
                "active": false
            },
            {
                "title": 1,
                "link": "http://http://localhost/api/items?per_page=10&page=1",
                "active": true
            },
            {
                "title": 2,
                "link": "http://http://localhost/api/items?per_page=10&page=2",
                "active": false
            },
            {
                "title": "Next",
                "link": "http://http://localhost/api/items?per_page=10&page=2",
                "active": false
            }
        ],
        "page": "1",
        "total_page": 2
    }
}
            </pre>

            <h4>Example Request for Selection Mode</h4>
            <pre>
GET {{ env('SERVER_DOMAIN') }}/api/items?mode=selection
                <button class="copy-button" onclick="copyToClipboard('{{ env('SERVER_DOMAIN') }}/api/items?mode=selection')">
                    <i class="fas fa-copy"></i> Copy URL
                </button>
            </pre>

            <h4>Example Response for Selection Mode</h4>
            <pre>
{
    "data": [
        {
            "id": 1,
            "name": "Stethoscope",
            "estimated_budget": 0,
            "unit": "pc",
            "category": "MED-EQ",
            "classification": "DIAG-INST",
            "item_unit": {
                "id": 1,
                "name": "Piece",
                "code": "pc",
                "description": "Individual countable items (e.g., stethoscopes, microscopes)",
                "deleted_at": null,
                "created_at": "2025-03-24T18:23:45.000000Z",
                "updated_at": "2025-03-24T18:23:45.000000Z"
            },
            "item_category": {
                "id": 1,
                "name": "Medical Equipment",
                "code": "MED-EQ",
                "description": "Durable medical devices used for diagnosis, monitoring or treatment (e.g., ventilators, ECG machines)",
                "deleted_at": null,
                "created_at": "2025-03-24T18:27:37.000000Z",
                "updated_at": "2025-03-24T18:27:37.000000Z"
            },
            "item_classification": {
                "id": 1,
                "item_category_id": 6,
                "name": "Diagnostic Instruments",
                "code": "DIAG-INST",
                "description": "Handheld or portable devices for physical examination (e.g., stethoscopes, otoscopes)",
                "deleted_at": null,
                "created_at": "2025-03-24T18:38:46.000000Z",
                "updated_at": "2025-03-24T18:38:46.000000Z"
            },
            "created_at": "2025-03-24T18:44:44.000000Z",
            "updated_at": "2025-03-24T18:44:44.000000Z"
        },
        {
            "id": 2,
            "name": "Stethoscope",
            "estimated_budget": 150,
            "unit": "pc",
            "category": "MED-EQ",
            "classification": "DIAG-INST",
            "item_unit": {
                "id": 1,
                "name": "Piece",
                "code": "pc",
                "description": "Individual countable items (e.g., stethoscopes, microscopes)",
                "deleted_at": null,
                "created_at": "2025-03-24T18:23:45.000000Z",
                "updated_at": "2025-03-24T18:23:45.000000Z"
            },
            "item_category": {
                "id": 1,
                "name": "Medical Equipment",
                "code": "MED-EQ",
                "description": "Durable medical devices used for diagnosis, monitoring or treatment (e.g., ventilators, ECG machines)",
                "deleted_at": null,
                "created_at": "2025-03-24T18:27:37.000000Z",
                "updated_at": "2025-03-24T18:27:37.000000Z"
            },
            "item_classification": {
                "id": 1,
                "item_category_id": 6,
                "name": "Diagnostic Instruments",
                "code": "DIAG-INST",
                "description": "Handheld or portable devices for physical examination (e.g., stethoscopes, otoscopes)",
                "deleted_at": null,
                "created_at": "2025-03-24T18:38:46.000000Z",
                "updated_at": "2025-03-24T18:38:46.000000Z"
            },
            "created_at": "2025-03-24T19:01:41.000000Z",
            "updated_at": "2025-03-24T19:01:41.000000Z"
        },
        ...
    ],
    "metadata": {
        "methods": "[GET, POST, PUT, DELETE]",
        "content": "This type of response is for selection component.",
        "mode": "selection"
    }
}
            </pre>

            <h4>Example Request for Single Record by ID</h4>
            <pre>
GET {{ env('SERVER_DOMAIN') }}/api/items?item_unit_id=1
                <button class="copy-button" onclick="copyToClipboard('{{ env('SERVER_DOMAIN') }}/api/items?item_unit_id=1')">
                    <i class="fas fa-copy"></i> Copy URL
                </button>
            </pre>

            <h4>Example Response for Single Record by ID</h4>
            <pre>
{
    "data": {
        "id": 1,
        "name": "Stethoscope",
        "estimated_budget": 0,
        "unit": "pc",
        "category": "MED-EQ",
        "classification": "DIAG-INST",
        "item_unit": {
            "id": 1,
            "name": "Piece",
            "code": "pc",
            "description": "Individual countable items (e.g., stethoscopes, microscopes)",
            "deleted_at": null,
            "created_at": "2025-03-24T18:23:45.000000Z",
            "updated_at": "2025-03-24T18:23:45.000000Z"
        },
        "item_category": {
            "id": 1,
            "name": "Medical Equipment",
            "code": "MED-EQ",
            "description": "Durable medical devices used for diagnosis, monitoring or treatment (e.g., ventilators, ECG machines)",
            "deleted_at": null,
            "created_at": "2025-03-24T18:27:37.000000Z",
            "updated_at": "2025-03-24T18:27:37.000000Z"
        },
        "item_classification": {
            "id": 1,
            "item_category_id": 6,
            "name": "Diagnostic Instruments",
            "code": "DIAG-INST",
            "description": "Handheld or portable devices for physical examination (e.g., stethoscopes, otoscopes)",
            "deleted_at": null,
            "created_at": "2025-03-24T18:38:46.000000Z",
            "updated_at": "2025-03-24T18:38:46.000000Z"
        },
        "created_at": "2025-03-24T18:44:44.000000Z",
        "updated_at": "2025-03-24T18:44:44.000000Z"
    },
    "metadata": {
        "methods": "[GET, POST, PUT, DELETE]",
        "urls": [
            "http://localhost:8000/api/items?item_id=[primary-key]",
            "http://localhost:8000/api/items?page={currentPage}&per_page={number_of_record_to_return}",
            "http://localhost:8000/api/items?page={currentPage}&per_page={number_of_record_to_return}&mode=selection",
            "http://localhost:8000/api/items?page={currentPage}&per_page={number_of_record_to_return}&search=value"
        ]
    }
}
            </pre>

            <h3>Error Responses</h3>
            <table>
                <thead>
                    <tr>
                        <th>Status Code</th>
                        <th>Message</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>404</td>
                        <td>No record found.</td>
                        <td>Returned when no item unit is found for the given <code>item_unit_id</code>.</td>
                    </tr>
                    <tr>
                        <td>422</td>
                        <td>Invalid request.</td>
                        <td>Returned when invalid parameters are provided.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Store Endpoint -->
        <div class="endpoint">
            <h2>POST /api/items</h2>
            <p>Create a new item category or insert multiple item categories in bulk.</p>

            <h3>Request Body</h3>
            <table>
                <thead>
                    <tr>
                        <th>Parameter</th>
                        <th>Type</th>
                        <th>Description</th>
                        <th>Required</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>name</td>
                        <td>string</td>
                        <td>The name of the item category.</td>
                        <td>Yes (for single insert)</td>
                    </tr>
                    <tr>
                        <td>estimated_budget</td>
                        <td>float</td>
                        <td>The market price.</td>
                        <td>Yes (for single insert)</td>
                    </tr>
                    <tr>
                        <td>item_unit_id</td>
                        <td>integer</td>
                        <td>A referrence of item unit resource.</td>
                        <td>No</td>
                    </tr>
                    <tr>
                        <td>item_category_id</td>
                        <td>integer</td>
                        <td>A referrence of item category resource.</td>
                        <td>No</td>
                    </tr>
                    <tr>
                        <td>item_classification_id</td>
                        <td>integer</td>
                        <td>A referrence of item classification resource.</td>
                        <td>No</td>
                    </tr>
                    <tr>
                        <td>items</td>
                        <td>array</td>
                        <td>
                            An array of item categories for bulk insert. Each item in the array should include:
                            <ul>
                                <li><code>name</code> (string, required)</li>
                                <li><code>estimated_budget</code> (float, required)</li>
                                <li><code>item_unit_id</code> (integer, required)</li>
                                <li><code>item_category_id</code> (integer, required)</li>
                                <li><code>item_classification_id</code> (integer, required)</li>
                            </ul>
                        </td>
                        <td>Yes (for bulk insert)</td>
                    </tr>
                </tbody>
            </table>

            <h3>Example Request for Single Insert</h3>
            <pre>
        POST {{ env('SERVER_DOMAIN') }}/api/items
        Content-Type: application/json
{
    "name": "Stethoscope",
    "code": "st",
    "item_classification_id": 1,
    "item_unit_id": 1,
    "item_category_id": 1,
    "estimated_budget": 150.00,
}
                <button class="copy-button" onclick="copyToClipboard('{{ env('SERVER_DOMAIN') }}/api/items')">
                    <i class="fas fa-copy"></i> Copy URL
                </button>
            </pre>

            <h3>Example Request for Bulk Insert</h3>
            <pre>
POST {{ env('SERVER_DOMAIN') }}/api/items
Content-Type: application/json

{
    "items": [
        {
            "name": "Stethoscope",
            "code": "st",
            "item_classification_id": 1,
            "item_unit_id": 1,
            "item_category_id": 1,
            "estimated_budget": 150.00
        },
        {
            "name": "Scalpel",
            "code": "sc",
            "item_classification_id": 2,
            "item_unit_id": 1,
            "item_category_id": 2,
            "estimated_budget": 8.50
        },
        {
            "name": "Microscope",
            "code": "ms",
            "item_classification_id": 1,
            "item_unit_id": 1,
            "item_category_id": 1,
            "estimated_budget": 2500.00
        },
    ]
}
                <button class="copy-button" onclick="copyToClipboard('{{ env('SERVER_DOMAIN') }}/api/items')">
                    <i class="fas fa-copy"></i> Copy URL
                </button>
            </pre>

            <h3>Example Response for Single Insert</h3>
            <pre>
{
    "data": {
        "id": 1,
        "name": "Stethoscope",
        "estimated_budget": null,
        "unit": "pc",
        "category": "MED-EQ",
        "classification": "DIAG-INST",
        "item_unit": {
            "id": 1,
            "name": "Piece",
            "code": "pc",
            "description": "Individual countable items (e.g., stethoscopes, microscopes)",
            "deleted_at": null,
            "created_at": "2025-03-24T18:23:45.000000Z",
            "updated_at": "2025-03-24T18:23:45.000000Z"
        },
        "item_category": {
            "id": 1,
            "name": "Medical Equipment",
            "code": "MED-EQ",
            "description": "Durable medical devices used for diagnosis, monitoring or treatment (e.g., ventilators, ECG machines)",
            "deleted_at": null,
            "created_at": "2025-03-24T18:27:37.000000Z",
            "updated_at": "2025-03-24T18:27:37.000000Z"
        },
        "item_classification": {
            "id": 1,
            "item_category_id": 6,
            "name": "Diagnostic Instruments",
            "code": "DIAG-INST",
            "description": "Handheld or portable devices for physical examination (e.g., stethoscopes, otoscopes)",
            "deleted_at": null,
            "created_at": "2025-03-24T18:38:46.000000Z",
            "updated_at": "2025-03-24T18:38:46.000000Z"
        },
        "created_at": "2025-03-24T18:44:44.000000Z",
        "updated_at": "2025-03-24T18:44:44.000000Z"
    },
    "message": "Successfully created items record.",
    "metadata": {
        "methods": [
            "GET, POST, PUT, DELETE"
        ]
    }
}
            </pre>

            <h3>Example Response for Bulk Insert</h3>
            <pre>
{
    "data": [
        {
            "id": 3,
            "name": "Scalpel",
            "estimated_budget": 8.5,
            "unit": "pc",
            "category": "SURG-SUP",
            "classification": "SURG-INST",
            "item_unit": {
                "id": 1,
                "name": "Piece",
                "code": "pc",
                "description": "Individual countable items (e.g., stethoscopes, microscopes)",
                "deleted_at": null,
                "created_at": "2025-03-24T18:23:45.000000Z",
                "updated_at": "2025-03-24T18:23:45.000000Z"
            },
            "item_category": {
                "id": 2,
                "name": "Surgical Supplies",
                "code": "SURG-SUP",
                "description": "Instruments and materials used in surgical procedures (e.g., scalpels, sutures, drapes)",
                "deleted_at": null,
                "created_at": "2025-03-24T18:27:59.000000Z",
                "updated_at": "2025-03-24T18:27:59.000000Z"
            },
            "item_classification": {
                "id": 2,
                "item_category_id": 2,
                "name": "Surgical Instruments",
                "code": "SURG-INST",
                "description": "Tools for performing surgical procedures (e.g., scalpels, forceps, retractors)",
                "deleted_at": null,
                "created_at": "2025-03-24T18:39:54.000000Z",
                "updated_at": "2025-03-24T18:39:54.000000Z"
            },
            "created_at": "2025-03-24T19:01:41.000000Z",
            "updated_at": "2025-03-24T19:01:41.000000Z"
        },
        {
            "id": 4,
            "name": "Microscope",
            "estimated_budget": 2500,
            "unit": "pc",
            "category": "MED-EQ",
            "classification": "DIAG-INST",
            "item_unit": {
                "id": 1,
                "name": "Piece",
                "code": "pc",
                "description": "Individual countable items (e.g., stethoscopes, microscopes)",
                "deleted_at": null,
                "created_at": "2025-03-24T18:23:45.000000Z",
                "updated_at": "2025-03-24T18:23:45.000000Z"
            },
            "item_category": {
                "id": 1,
                "name": "Medical Equipment",
                "code": "MED-EQ",
                "description": "Durable medical devices used for diagnosis, monitoring or treatment (e.g., ventilators, ECG machines)",
                "deleted_at": null,
                "created_at": "2025-03-24T18:27:37.000000Z",
                "updated_at": "2025-03-24T18:27:37.000000Z"
            },
            "item_classification": {
                "id": 1,
                "item_category_id": 6,
                "name": "Diagnostic Instruments",
                "code": "DIAG-INST",
                "description": "Handheld or portable devices for physical examination (e.g., stethoscopes, otoscopes)",
                "deleted_at": null,
                "created_at": "2025-03-24T18:38:46.000000Z",
                "updated_at": "2025-03-24T18:38:46.000000Z"
            },
            "created_at": "2025-03-24T19:01:41.000000Z",
            "updated_at": "2025-03-24T19:01:41.000000Z"
        },
    ],
    "message": "Successfully created itemss record",
    "metadata": {
        "methods": "[GET, POST, PUT ,DELETE]",
        "duplicate_items": []
    }
}
            </pre>

            <h3>Error Responses</h3>
            <table>
                <thead>
                    <tr>
                        <th>Status Code</th>
                        <th>Message</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>422</td>
                        <td>Validation error.</td>
                        <td>Returned when the request body is invalid (e.g., missing required fields).</td>
                    </tr>
                    <tr>
                        <td>500</td>
                        <td>Internal server error.</td>
                        <td>Returned when an unexpected error occurs during processing.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Put Endpoint -->
        <div class="endpoint">
            <h2>PUT /api/items</h2>
            <p>Update one or more items. Supports both single and bulk updates.</p>
            
            <h3>Parameters</h3>
            <table>
                <thead>
                    <tr>
                        <th>Parameter</th>
                        <th>Type</th>
                        <th>Description</th>
                        <th>Required</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>id</td>
                        <td>integer or array</td>
                        <td>The ID(s) of the item(s) to update. Can be a single ID or array of IDs (e.g., id[]=1&id[]=2)</td>
                        <td>Yes</td>
                    </tr>
                </tbody>
            </table>

            <h3>Request Body</h3>
            <table>
                <thead>
                    <tr>
                        <th>Field</th>
                        <th>Type</th>
                        <th>Description</th>
                        <th>Required</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>name</td>
                        <td>string</td>
                        <td>The name of the item</td>
                        <td>Yes (for single update)</td>
                    </tr>
                    <tr>
                        <td>estimated_budget</td>
                        <td>float</td>
                        <td>The market price of the item</td>
                        <td>Yes (for single update)</td>
                    </tr>
                    <tr>
                        <td>item_unit_id</td>
                        <td>integer</td>
                        <td>Reference to item unit resource</td>
                        <td>No</td>
                    </tr>
                    <tr>
                        <td>item_category_id</td>
                        <td>integer</td>
                        <td>Reference to item category resource</td>
                        <td>No</td>
                    </tr>
                    <tr>
                        <td>item_classification_id</td>
                        <td>integer</td>
                        <td>Reference to item classification resource</td>
                        <td>No</td>
                    </tr>
                    <tr>
                        <td>items</td>
                        <td>array</td>
                        <td>
                            Array of items for bulk update (required when multiple IDs provided). Each item should include:
                            <ul>
                                <li><code>name</code> (string, required)</li>
                                <li><code>estimated_budget</code> (float, required)</li>
                                <li><code>item_unit_id</code> (integer, optional)</li>
                                <li><code>item_category_id</code> (integer, optional)</li>
                                <li><code>item_classification_id</code> (integer, optional)</li>
                            </ul>
                        </td>
                        <td>Yes (for bulk update)</td>
                    </tr>
                </tbody>
            </table>

            <h3>Example Requests</h3>
            <h4>Single Item Update</h4>
            <pre>
PUT {{ env('SERVER_DOMAIN') }}/api/items?id=1
Content-Type: application/json

{
    "name": "Premium Stethoscope",
    "estimated_budget": 195.50,
    "item_unit_id": 1,
    "item_category_id": 1,
    "item_classification_id": 1
}
            <button class="copy-button" onclick="copyToClipboard('PUT {{ env('SERVER_DOMAIN') }}/api/items?id=1')">
                <i class="fas fa-copy"></i> Copy
            </button>
            </pre>

            <h4>Bulk Update</h4>
            <pre>
PUT {{ env('SERVER_DOMAIN') }}/api/items?id[]=1&id[]=2
Content-Type: application/json

{
    "items": [
        {
            "name": "Premium Stethoscope",
            "estimated_budget": 195.50,
            "item_unit_id": 1
        },
        {
            "name": "Surgical Scalpel",
            "estimated_budget": 9.75,
            "item_category_id": 2
        }
    ]
}
            <button class="copy-button" onclick="copyToClipboard('PUT {{ env('SERVER_DOMAIN') }}/api/items?id[]=1&id[]=2')">
                <i class="fas fa-copy"></i> Copy
            </button>
            </pre>

            <h3>Example Single Response</h3>
            <h4>Success Response</h4>
            <pre>
{
    "data": {
        "id": 1,
        "name": "Premium Stethoscope",
        "deleted_at": null,
        "created_at": "2025-03-24T18:23:45.000000Z",
        "updated_at": "2025-03-24T19:44:25.000000Z"
    },
    "metadata": {
        "methods": "[GET, PUT, DELETE]",
        "formats": [
            "http://localhost:8000/api/item-units?id=1",
            "http://localhost:8000/api/item-units?id=1,2,3"
        ]
    }
}
            </pre>

            <h3>Example Responses</h3>
            <h4>Success Response</h4>
            <pre>
{
    "data": [
        {
            "id": 1,
            "name": "Premium Stethoscope",
            "estimated_budget": "195.5",
            "unit": "",
            "category": "MED-EQ",
            "classification": "DIAG-INST",
            "item_unit": {
                "id": 1,
                "name": "",
                "code": "",
                "description": "",
                "deleted_at": null,
                "created_at": "2025-03-24T18:23:45.000000Z",
                "updated_at": "2025-03-24T19:47:44.000000Z"
            },
            "item_category": {
                "id": 1,
                "name": "Medical Equipment",
                "code": "MED-EQ",
                "description": "Durable medical devices used for diagnosis, monitoring or treatment (e.g., ventilators, ECG machines)",
                "deleted_at": null,
                "created_at": "2025-03-24T18:27:37.000000Z",
                "updated_at": "2025-03-24T18:27:37.000000Z"
            },
            "item_classification": {
                "id": 1,
                "item_category_id": 6,
                "name": "Diagnostic Instruments",
                "code": "DIAG-INST",
                "description": "Handheld or portable devices for physical examination (e.g., stethoscopes, otoscopes)",
                "deleted_at": null,
                "created_at": "2025-03-24T18:38:46.000000Z",
                "updated_at": "2025-03-24T18:38:46.000000Z"
            },
            "created_at": "2025-03-24T18:44:44.000000Z",
            "updated_at": "2025-03-24T19:58:41.000000Z"
        },
        {
            "id": 2,
            "name": "Surgical Scalpel",
            "estimated_budget": "9.75",
            "unit": "",
            "category": "SURG-SUP",
            "classification": "DIAG-INST",
            "item_unit": {
                "id": 1,
                "name": "",
                "code": "",
                "description": "",
                "deleted_at": null,
                "created_at": "2025-03-24T18:23:45.000000Z",
                "updated_at": "2025-03-24T19:47:44.000000Z"
            },
            "item_category": {
                "id": 2,
                "name": "Surgical Supplies",
                "code": "SURG-SUP",
                "description": "Instruments and materials used in surgical procedures (e.g., scalpels, sutures, drapes)",
                "deleted_at": null,
                "created_at": "2025-03-24T18:27:59.000000Z",
                "updated_at": "2025-03-24T18:27:59.000000Z"
            },
            "item_classification": {
                "id": 1,
                "item_category_id": 6,
                "name": "Diagnostic Instruments",
                "code": "DIAG-INST",
                "description": "Handheld or portable devices for physical examination (e.g., stethoscopes, otoscopes)",
                "deleted_at": null,
                "created_at": "2025-03-24T18:38:46.000000Z",
                "updated_at": "2025-03-24T18:38:46.000000Z"
            },
            "created_at": "2025-03-24T19:01:41.000000Z",
            "updated_at": "2025-03-24T19:58:41.000000Z"
        }
    ],
    "message": "Successfully updated 2 items.",
    "metadata": {
        "method": "[GET, POST, PUT, DELETE]"
    }
}
            </pre>

            <h4>Partial Update Response</h4>
            <pre>
{
    "data": [
        {
            "id": 1,
            "name": "Premium Stethoscope",
            "estimated_budget": "195.5",
            "unit": "",
            "category": "MED-EQ",
            "classification": "DIAG-INST",
            "item_unit": {
                "id": 1,
                "name": "",
                "code": "",
                "description": "",
                "deleted_at": null,
                "created_at": "2025-03-24T18:23:45.000000Z",
                "updated_at": "2025-03-24T19:47:44.000000Z"
            },
            "item_category": {
                "id": 1,
                "name": "Medical Equipment",
                "code": "MED-EQ",
                "description": "Durable medical devices used for diagnosis, monitoring or treatment (e.g., ventilators, ECG machines)",
                "deleted_at": null,
                "created_at": "2025-03-24T18:27:37.000000Z",
                "updated_at": "2025-03-24T18:27:37.000000Z"
            },
            "item_classification": {
                "id": 1,
                "item_category_id": 6,
                "name": "Diagnostic Instruments",
                "code": "DIAG-INST",
                "description": "Handheld or portable devices for physical examination (e.g., stethoscopes, otoscopes)",
                "deleted_at": null,
                "created_at": "2025-03-24T18:38:46.000000Z",
                "updated_at": "2025-03-24T18:38:46.000000Z"
            },
            "created_at": "2025-03-24T18:44:44.000000Z",
            "updated_at": "2025-03-24T19:58:41.000000Z"
        }
    ],
    "message": "Partial update completed with errors.",
    "metadata": {
        "method": "[PUT]",
        "errors": [
            "Item with ID 254 not found."
        ]
    }
}
            </pre>

            <h3>Error Responses</h3>
            <table>
                <thead>
                    <tr>
                        <th>Status Code</th>
                        <th>Message</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>400</td>
                        <td>ID parameter is required</td>
                        <td>Returned when no ID parameter is provided</td>
                    </tr>
                    <tr>
                        <td>404</td>
                        <td>Item not found</td>
                        <td>Returned when no item exists with the provided ID</td>
                    </tr>
                    <tr>
                        <td>409</td>
                        <td>Number of IDs does not match number of items</td>
                        <td>Returned for bulk updates when ID count doesn't match items array length</td>
                    </tr>
                    <tr>
                        <td>422</td>
                        <td>Validation error</td>
                        <td>Returned when required fields are missing or invalid</td>
                    </tr>
                    <tr>
                        <td>424</td>
                        <td>Partial update completed with errors</td>
                        <td>Returned when some items in bulk update failed</td>
                    </tr>
                </tbody>
            </table>
        </div>
    
        <!-- Delete Endpoint -->
        <div class="endpoint">
            <h2>DELETE /api/items</h2>
            <p>Delete one or more item categories. Deletion is performed via soft delete (sets deleted_at timestamp).</p>

            <h3>Parameters</h3>
            <table>
                <thead>
                    <tr>
                        <th>Parameter</th>
                        <th>Type</th>
                        <th>Description</th>
                        <th>Required</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>id</td>
                        <td>integer or array</td>
                        <td>The ID(s) of the item category(s) to delete. Can be a single ID or comma-separated list.</td>
                        <td>Yes (if no other parameter provided)</td>
                    </tr>
                    <tr>
                        <td>query</td>
                        <td>object</td>
                        <td>Query object to find records (e.g., <code>{"code": "MED-EQ"}</code> or <code>{"name": "Medical Equipment"}</code>)</td>
                        <td>Yes (if no ID provided)</td>
                    </tr>
                    <tr>
                        <td>query[name]</td>
                        <td>string</td>
                        <td>Direct name query (alternative to full query object). Example: <code>query[name]=Medical Equipment</code></td>
                        <td>No</td>
                    </tr>
                </tbody>
            </table>

            <h3>Example Requests</h3>
            <h4>Delete by ID</h4>
            <pre>
        DELETE {{ env('SERVER_DOMAIN') }}/api/items?id=1
                <button class="copy-button" onclick="copyToClipboard('{{ env('SERVER_DOMAIN') }}/api/items?id=1')">
                    <i class="fas fa-copy"></i> <span>Copy URL</span>
                </button>
            </pre>

            <h4>Delete by Query Object</h4>
            <pre>
        DELETE {{ env('SERVER_DOMAIN') }}/api/items?query={"code":"MED-EQ"}
                <button class="copy-button" onclick="copyToClipboard('{{ env('SERVER_DOMAIN') }}/api/items?query={"code":"MED-EQ"}')">
                    <i class="fas fa-copy"></i> <span>Copy URL</span>
                </button>
            </pre>

            <h4>Delete by Name Query</h4>
            <pre>
        DELETE {{ env('SERVER_DOMAIN') }}/api/items?query[name]=Medical%20Equipment
                <button class="copy-button" onclick="copyToClipboard('{{ env('SERVER_DOMAIN') }}/api/items?query[name]=Medical%20Equipment')">
                    <i class="fas fa-copy"></i> <span>Copy URL</span>
                </button>
            </pre>

            <h3>Example Response</h3>
            <pre>
        {
            "message": "Successfully deleted 1 record."
        }
            </pre>

            <h3>Error Responses</h3>
            <table>
                <thead>
                    <tr>
                        <th>Status Code</th>
                        <th>Message</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>422</td>
                        <td>Invalid request.</td>
                        <td>No parameters provided. In development mode, returns available methods and formats.</td>
                    </tr>
                    <tr>
                        <td>404</td>
                        <td>No records found.</td>
                        <td>No matching records found for the given parameters.</td>
                    </tr>
                    <tr>
                        <td>409</td>
                        <td>Request has multiple records.</td>
                        <td>Returned when query matches multiple records (includes data of all matches).</td>
                    </tr>
                </tbody>
            </table>

            <h3>Implementation Details</h3>
            <p>The endpoint supports three deletion methods:</p>
            <ol>
                <li><strong>Direct ID(s)</strong>: Delete by primary key(s)</li>
                <li><strong>Query Object</strong>: Flexible query using any field(s)</li>
                <li><strong>Name Query</strong>: Simplified syntax for name-based deletion</li>
            </ol>
            <p>In development mode, the endpoint provides additional metadata about available parameters and formats when invalid requests are made.</p>
        </div>

<!-- Notification Message -->
<div id="copy-notification" class="copy-notification"></div>

<script>
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            const notification = document.getElementById('copy-notification');
            notification.innerText = `Copied: ${text}`;
            notification.style.display = 'block';

            // Hide after 3 seconds
            setTimeout(() => {
                notification.style.display = 'none';
            }, 3000);
        }).catch(err => {
            console.error('Failed to copy URL: ', err);
        });
    }
</script>
</body>
</html>