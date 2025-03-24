<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Documentation - Item Units</title>
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

            .unit-examples {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="{{ url('/api-docs') }}" class="back-link">← Back to API Documentation</a>
        <h1>API Documentation - Item Units</h1>
        
        <div class="resource-description">
            <h2>Item Units Resource</h2>
            <p>
                The Item Units resource defines standard units of measurement used across inventory items.
                These units provide consistency in quantifying and tracking inventory quantities.
            </p>
            
            <h3>Key Features</h3>
            <ul>
                <li>Standardize measurement units across all inventory items</li>
                <li>Support for both simple and complex unit definitions</li>
                <li>Enable accurate quantity tracking and reporting</li>
                <li>Soft deletion capability for maintaining historical data</li>
            </ul>
        </div>

        <div class="model-fields">
            <h2>Item Unit Model Fields</h2>
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
                        <td>name</td>
                        <td>string</td>
                        <td>Full name of the measurement unit</td>
                        <td>Yes</td>
                        <td>"Milliliter", "Box"</td>
                    </tr>
                    <tr>
                        <td>code</td>
                        <td>string</td>
                        <td>Short code/abbreviation for the unit</td>
                        <td>Yes</td>
                        <td>"ml", "bx"</td>
                    </tr>
                    <tr>
                        <td>description</td>
                        <td>string</td>
                        <td>Detailed explanation of the unit's usage</td>
                        <td>No</td>
                        <td>"Standard 5ml medical syringe measurement"</td>
                    </tr>
                    <tr>
                        <td>deleted_at</td>
                        <td>timestamp</td>
                        <td>Soft deletion timestamp (null if active)</td>
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
                        <td>Record last update timestamp</td>
                        <td>Auto</td>
                        <td>2023-06-15 10:30:00</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="usage-notes">
            <h2>Usage Notes</h2>
            <ul>
                <li>Unit codes should be unique across the system</li>
                <li>Once created, units should not be deleted but rather marked inactive</li>
                <li>Existing units should only be modified if they haven't been used in transactions</li>
                <li>Consider localization requirements for unit names</li>
            </ul>
        </div>

        <!-- Index Endpoint -->
        <div class="endpoint">
            <h2>GET /api/item-units</h2>
            <p>Retrieve item units with options for pagination, selection mode, or fetching a single record by ID.</p>

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
                        <td>A search term to filter item units by name.</td>
                        <td>No</td>
                    </tr>
                    <tr>
                        <td>item_unit_id</td>
                        <td>integer</td>
                        <td>The ID of a specific item unit to retrieve.</td>
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
                    <strong>selection</strong>: Returns a flat list of item units suitable for use in dropdowns or selection components.
                </li>
            </ul>

            <h4>Example Request for Pagination Mode</h4>
            <pre>
GET {{ env('SERVER_DOMAIN') }}/api/item-units?page=1&per_page=10&search=Ton
                <button class="copy-button" onclick="copyToClipboard('{{ env('SERVER_DOMAIN') }}/api/item-units?page=1&per_page=10&search=Ton')">
                    <i class="fas fa-copy"></i> Copy URL
                </button>
            </pre>

            <h4>Example Response for Pagination Mode</h4>
            <pre>
{
    "data": [
        {
            "id": 1,
            "name": "Ton",
            "code": "T"
        }
    ],
    "metadata": {
        "pagination": [
            {
                "title": "previous",
                "link": null,
                "is_active": false
            },
            {
                "title": 1,
                "link": "http://localhost:8000/api/item-units?search=Ton&per_page=10&last_initial_id=0&last_id=0",
                "is_active": true
            },
            {
                "title": 2,
                "link": "http://localhost:8000/api/item-units?search=Ton&per_page=10&last_initial_id=7&last_id=20",
                "is_active": false
            },
            {
                "title": "next",
                "link": "http://localhost:8000/api/item-units?search=Ton&per_page=10&last_initial_id=0&last_id=0",
                "is_active": true
            }
        ],
        "page": 1,
        "total_page": 2
    }
}
            </pre>

            <h4>Example Request for Selection Mode</h4>
            <pre>
GET {{ env('SERVER_DOMAIN') }}/api/item-units?mode=selection
                <button class="copy-button" onclick="copyToClipboard('{{ env('SERVER_DOMAIN') }}/api/item-units?mode=selection')">
                    <i class="fas fa-copy"></i> Copy URL
                </button>
            </pre>

            <h4>Example Response for Selection Mode</h4>
            <pre>
{
    "data": [
        {
            "id": 4,
            "name": "Ounces",
            "code": "ozx"
        },
        {
            "id": 5,
            "name": "Liter",
            "code": "L"
        },
        {
            "id": 6,
            "name": "Milliliter",
            "code": "mL"
        },
        {
            "id": 7,
            "name": "Ton",
            "code": "t"
        }
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
GET {{ env('SERVER_DOMAIN') }}/api/item-units?item_unit_id=1
                <button class="copy-button" onclick="copyToClipboard('{{ env('SERVER_DOMAIN') }}/api/item-units?item_unit_id=1')">
                    <i class="fas fa-copy"></i> Copy URL
                </button>
            </pre>

            <h4>Example Response for Single Record by ID</h4>
            <pre>
{
    "data": {
        "id": 1,
        "name": "Ton",
        "code": "T"
    },
    "metadata": {
        "methods": "[GET, POST, PUT, DELETE]",
        "urls": [
            "{{ env('SERVER_DOMAIN') }}/api/item-units?item_unit_id=1",
            "{{ env('SERVER_DOMAIN') }}/api/item-units?page=1&per_page=10",
            "{{ env('SERVER_DOMAIN') }}/api/item-units?page=1&per_page=10&mode=selection",
            "{{ env('SERVER_DOMAIN') }}/api/item-units?page=1&per_page=10&search=Ton"
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
            <h2>POST /api/item-units</h2>
            <p>Create a new item unit or insert multiple item units in bulk.</p>

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
                        <td>The name of the item unit.</td>
                        <td>Yes (for single insert)</td>
                    </tr>
                    <tr>
                        <td>code</td>
                        <td>string</td>
                        <td>The code of the item unit.</td>
                        <td>Yes (for single insert)</td>
                    </tr>
                    <tr>
                        <td>description</td>
                        <td>string</td>
                        <td>A description of the item unit.</td>
                        <td>No</td>
                    </tr>
                    <tr>
                        <td>item_units</td>
                        <td>array</td>
                        <td>
                            An array of item units for bulk insert. Each item in the array should include:
                            <ul>
                                <li><code>name</code> (string, required)</li>
                                <li><code>code</code> (string, required)</li>
                                <li><code>description</code> (string, optional)</li>
                            </ul>
                        </td>
                        <td>Yes (for bulk insert)</td>
                    </tr>
                </tbody>
            </table>

            <h3>Example Request for Single Insert</h3>
            <pre>
        POST {{ env('SERVER_DOMAIN') }}/api/item-units
        Content-Type: application/json

{
    "name": "Piece",
    "code": "pc",
    "description": "Individual countable items (e.g., stethoscopes, microscopes)",
}
                <button class="copy-button" onclick="copyToClipboard('{{ env('SERVER_DOMAIN') }}/api/item-units')">
                    <i class="fas fa-copy"></i> Copy URL
                </button>
            </pre>

            <h3>Example Request for Bulk Insert</h3>
            <pre>
POST {{ env('SERVER_DOMAIN') }}/api/item-units
Content-Type: application/json

{
    "item_units": [
        {
            "name": "Box",
            "code": "box",
            "description": "Pre-packaged quantities of items (e.g., boxes of gloves, syringes)"
        },
        {
            "name": "Pack",
            "code": "pack",
            "description": "Bundled items sold together (e.g., dressing packs, suture kits)"
        }
    ]
}
                <button class="copy-button" onclick="copyToClipboard('{{ env('SERVER_DOMAIN') }}/api/item-units')">
                    <i class="fas fa-copy"></i> Copy URL
                </button>
            </pre>

            <h3>Example Response for Single Insert</h3>
            <pre>
{
    "data": {
        "id": 1,
        "name": "Piece",
        "code": "pc",
        "description": "Individual countable items (e.g., stethoscopes, microscopes)",
        "updated_at": "2025-03-24T18:23:45.000000Z",
        "created_at": "2025-03-24T18:23:45.000000Z"
    },
    "message": "Successfully created item unit record.",
    "metadata": {
        "methods": [
            "GET, POST, PUT, DELET"
        ]
    }
}
            </pre>

            <h3>Example Response for Bulk Insert</h3>
            <pre>
{
    "data": [
        {
            "id": 2,
            "name": "Box",
            "code": "box",
            "description": "Pre-packaged quantities of items (e.g., boxes of gloves, syringes)",
            "deleted_at": null,
            "created_at": "2025-03-24T18:24:34.000000Z",
            "updated_at": "2025-03-24T18:24:34.000000Z"
        },
        {
            "id": 3,
            "name": "Pack",
            "code": "pack",
            "description": "Bundled items sold together (e.g., dressing packs, suture kits)",
            "deleted_at": null,
            "created_at": "2025-03-24T18:24:34.000000Z",
            "updated_at": "2025-03-24T18:24:34.000000Z"
        }
    ],
    "message": "Successfully created item units record",
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
            <h2>PUT /api/log-descriptions</h2>
            <p>Update existing log description records. Supports both single and bulk updates.</p>

            <h3>URL Parameters</h3>
            <table class="parameter-table">
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
                        <td>integer|array</td>
                        <td>
                            The ID(s) of the log description(s) to update.
                            <ul>
                                <li>Single update: <code>?id=1</code></li>
                                <li>Bulk update: <code>?id[]=1&id[]=2</code></li>
                            </ul>
                        </td>
                        <td>Yes</td>
                    </tr>
                </tbody>
            </table>

            <h3>Request Body</h3>
            <table class="field-table">
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
                        <td>title</td>
                        <td>string</td>
                        <td>Log event title</td>
                        <td>No</td>
                    </tr>
                    <tr>
                        <td>code</td>
                        <td>string</td>
                        <td>Unique event code</td>
                        <td>No</td>
                    </tr>
                    <tr>
                        <td>description</td>
                        <td>string</td>
                        <td>Detailed log template</td>
                        <td>No</td>
                    </tr>
                    <tr>
                        <td>log_descriptions</td>
                        <td>array</td>
                        <td>
                            Required for bulk updates. Array of objects containing:
                            <ul>
                                <li>title</li>
                                <li>code</li>
                                <li>description</li>
                            </ul>
                        </td>
                        <td>Conditional</td>
                    </tr>
                </tbody>
            </table>

            <h3>Examples</h3>
            
            <div class="example">
                <h4>Single Update</h4>
                <pre><code>PUT /api/log-descriptions?id=1
Content-Type: application/json

{
    "title": "Item Updated",
    "code": "ITEM_UPDATE"
}
            </code></pre>
                
                <h4>Response</h4>
                <pre><code>
{
    "data": {
        "id": 1,
        "title": "Item Updated",
        "code": "ITEM_UPDATE",
        "description": "Original description remains"
    },
    "message": "Log description updated successfully."
}
            </code></pre>
            </div>

            <div class="example">
                <h4>Bulk Update</h4>
                <pre><code>
PUT /api/log-descriptions?id[]=1&id[]=2
Content-Type: application/json

{
    "log_descriptions": [
        {"title": "New Title"},
        {"code": "NEW_CODE"}
    ]
}
            </code></pre>
                
                <h4>Response</h4>
                <pre><code>
{
    "data": [
        {
            "id": 1,
            "title": "New Title",
            "code": null,
            "description": null
        },
        {
            "id": 2,
            "title": null,
            "code": "NEW_CODE",
            "description": null
        }
    ],
    "message": "Successfully updated 2 log descriptions."
}
            </code></pre>
            </div>

            <h3>Error Responses</h3>
            <table class="error-table">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Message</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>400</td>
                        <td>ID parameter is required</td>
                        <td>Missing ID parameter</td>
                    </tr>
                    <tr>
                        <td>404</td>
                        <td>Log description not found</td>
                        <td>Invalid ID provided</td>
                    </tr>
                    <tr>
                        <td>409</td>
                        <td>ID/item count mismatch</td>
                        <td>Bulk update count mismatch</td>
                    </tr>
                    <tr>
                        <td>422</td>
                        <td>No valid fields provided</td>
                        <td>Empty or invalid request body</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Delete Endpoint -->
        <div class="endpoint">
            <h2>DELETE /api/item-units</h2>
            <p>Delete one or more item units.</p>

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
                        <td>The ID(s) of the item unit(s) to delete. Can be a single ID or a comma-separated list of IDs.</td>
                        <td>Yes (if <code>query</code> is not provided)</td>
                    </tr>
                    <tr>
                        <td>query</td>
                        <td>object</td>
                        <td>A query object to find the item unit(s) to delete (e.g., <code>{"code": "example"}</code>).</td>
                        <td>Yes (if <code>id</code> is not provided)</td>
                    </tr>
                </tbody>
            </table>

            <h3>Example Request</h3>
    <pre>
DELETE {{ env('SERVER_DOMAIN') }}/api/item-units?id=1
            <button class="copy-button" onclick="copyToClipboard('{{ env('SERVER_DOMAIN') }}/api/item-units?item_unit_id=1')">
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
                        <td>Returned when neither <code>id</code> nor <code>query</code> is provided.</td>
                    </tr>
                    <tr>
                        <td>404</td>
                        <td>No records found.</td>
                        <td>Returned when no item units are found for the given <code>id</code> or <code>query</code>.</td>
                    </tr>
                    <tr>
                        <td>409</td>
                        <td>Request has multiple records.</td>
                        <td>Returned when the <code>query</code> matches multiple records.</td>
                    </tr>
                </tbody>
            </table>
        </div>
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