<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Documentation - Item Categories</title>
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

        .implementation-guidelines {
            margin-top: 40px;
        }

        .guideline-card {
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .guideline-card h3 {
            margin-top: 0;
            color: #2c3e50;
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

            .category-grid {
                grid-template-columns: 1fr;
            }
            
            .resource-description,
            .model-fields,
            .implementation-guidelines {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="{{ url('/api-docs') }}" class="back-link">← Back to API Documentation</a>
        <h1>API Documentation - Item Categories</h1>

        
        <div class="resource-description">
            <h2>Item Categories Resource</h2>
            <p>
                The Item Categories resource organizes inventory items into logical groups for better management,
                reporting, and procurement planning. Categories help standardize inventory classification across
                the organization.
            </p>
            
            <h3>Key Features</h3>
            <ul>
                <li>Hierarchical organization of inventory items</li>
                <li>Standardized classification for reporting</li>
                <li>Simplified searching and filtering</li>
                <li>Budgeting and planning by category</li>
                <li>Soft deletion for historical tracking</li>
            </ul>
        </div>

        <div class="model-fields">
            <h2>Item Category Model Fields</h2>
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
                        <td>Descriptive name of the category</td>
                        <td>Yes</td>
                        <td>"Medical Equipment"</td>
                    </tr>
                    <tr>
                        <td>code</td>
                        <td>string</td>
                        <td>Short unique identifier code</td>
                        <td>Yes</td>
                        <td>"MED-EQ"</td>
                    </tr>
                    <tr>
                        <td>description</td>
                        <td>text</td>
                        <td>Detailed explanation of category scope</td>
                        <td>No</td>
                        <td>"Includes all durable medical devices"</td>
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

        <!-- Index Endpoint -->
        <div class="endpoint">
            <h2>GET /api/item-categories</h2>
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
                        <td>A search term to filter item categories by name.</td>
                        <td>No</td>
                    </tr>
                    <tr>
                        <td>item_category_id</td>
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
                    <strong>selection</strong>: Returns a flat list of item categories suitable for use in dropdowns or selection components.
                </li>
            </ul>

            <h4>Example Request for Pagination Mode</h4>
            <pre>
GET {{ env('SERVER_DOMAIN') }}/api/item-categories?page=1&per_page=10&search=Ton
                <button class="copy-button" onclick="copyToClipboard('{{ env('SERVER_DOMAIN') }}/api/item-categories?page=1&per_page=10&search=Ton')">
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
                "link": "http://localhost:8000/api/item-categories?search=Ton&per_page=10&last_initial_id=0&last_id=0",
                "is_active": true
            },
            {
                "title": 2,
                "link": "http://localhost:8000/api/item-categories?search=Ton&per_page=10&last_initial_id=7&last_id=20",
                "is_active": false
            },
            {
                "title": "next",
                "link": "http://localhost:8000/api/item-categories?search=Ton&per_page=10&last_initial_id=0&last_id=0",
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
GET {{ env('SERVER_DOMAIN') }}/api/item-categories?mode=selection
                <button class="copy-button" onclick="copyToClipboard('{{ env('SERVER_DOMAIN') }}/api/item-categories?mode=selection')">
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
GET {{ env('SERVER_DOMAIN') }}/api/item-categories?item_category_id=1
                <button class="copy-button" onclick="copyToClipboard('{{ env('SERVER_DOMAIN') }}/api/item-categories?item_category_id=1')">
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
            "{{ env('SERVER_DOMAIN') }}/api/item-categories?item_category_id=1",
            "{{ env('SERVER_DOMAIN') }}/api/item-categories?page=1&per_page=10",
            "{{ env('SERVER_DOMAIN') }}/api/item-categories?page=1&per_page=10&mode=selection",
            "{{ env('SERVER_DOMAIN') }}/api/item-categories?page=1&per_page=10&search=Ton"
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
                        <td>Returned when no item unit is found for the given <code>item_category_id</code>.</td>
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
            <h2>POST /api/item-categories</h2>
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
                        <td>code</td>
                        <td>string</td>
                        <td>The code of the item category.</td>
                        <td>Yes (for single insert)</td>
                    </tr>
                    <tr>
                        <td>description</td>
                        <td>string</td>
                        <td>A description of the item category.</td>
                        <td>No</td>
                    </tr>
                    <tr>
                        <td>item_categories</td>
                        <td>array</td>
                        <td>
                            An array of item categories for bulk insert. Each item in the array should include:
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
POST {{ env('SERVER_DOMAIN') }}/api/item-categories
Content-Type: application/json

{
    "name": "Medical Equipment",
    "code": "MED-EQ",
    "description": "Durable medical devices used for diagnosis, monitoring or treatment (e.g., ventilators, ECG machines)",
}
                <button class="copy-button" onclick="copyToClipboard('{{ env('SERVER_DOMAIN') }}/api/item-categories')">
                    <i class="fas fa-copy"></i> Copy URL
                </button>
            </pre>

            <h3>Example Request for Bulk Insert</h3>
            <pre>
POST {{ env('SERVER_DOMAIN') }}/api/item-categories
Content-Type: application/json

{
    "item_categories": [
        {
            "name": "Medical Equipment",
            "code": "MED-EQ",
            "description": "Durable medical devices used for diagnosis, monitoring or treatment (e.g., ventilators, ECG machines)"
        },
        {
            "name": "Surgical Supplies",
            "code": "SURG-SUP",
            "description": "Instruments and materials used in surgical procedures (e.g., scalpels, sutures, drapes)"
        },
        {
            "name": "Pharmaceuticals",
            "code": "PHARMA",
            "description": "Medications and drugs for therapeutic use (e.g., antibiotics, analgesics)"
        },
    ]
}
                <button class="copy-button" onclick="copyToClipboard('{{ env('SERVER_DOMAIN') }}/api/item-categories')">
                    <i class="fas fa-copy"></i> Copy URL
                </button>
            </pre>

            <h3>Example Response for Single Insert</h3>
            <pre>
{
    "data": {
        "name": "Medical Equipment",
        "code": "MED-EQ",
        "description": "Durable medical devices used for diagnosis, monitoring or treatment (e.g., ventilators, ECG machines)",
        "updated_at": "2025-03-24T18:27:37.000000Z",
        "created_at": "2025-03-24T18:27:37.000000Z",
        "id": 1
    },
    "message": "Successfully created item category record.",
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
            "name": "Surgical Supplies",
            "code": "SURG-SUP",
            "description": "Instruments and materials used in surgical procedures (e.g., scalpels, sutures, drapes)",
            "deleted_at": null,
            "created_at": "2025-03-24T18:27:59.000000Z",
            "updated_at": "2025-03-24T18:27:59.000000Z"
        },
        {
            "id": 3,
            "name": "Pharmaceuticals",
            "code": "PHARMA",
            "description": "Medications and drugs for therapeutic use (e.g., antibiotics, analgesics)",
            "deleted_at": null,
            "created_at": "2025-03-24T18:27:59.000000Z",
            "updated_at": "2025-03-24T18:27:59.000000Z"
        },
    ],
    "message": "Successfully created item categorys record",
    "metadata": {
        "methods": "[GET, POST, PUT ,DELETE]",
        "duplicate_items": [
            {
                "name": "Medical Equipment",
                "code": "MED-EQ"
            }
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
        <h2>PUT /api/item-categories</h2>
        <p>Update existing item categories. Supports both single and bulk updates with partial updates.</p>

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
                    <td>integer|string|array</td>
                    <td>
                        The ID(s) of the category(ies) to update.
                        <ul>
                            <li>Single update: <code>?id=1</code></li>
                            <li>Bulk update: <code>?id[]=1&id[]=2</code></li>
                            <li>Comma-separated: <code>?id=1,2,3</code></li>
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
                    <td>name</td>
                    <td>string</td>
                    <td>Category name</td>
                    <td>No (partial updates supported)</td>
                </tr>
                <tr>
                    <td>code</td>
                    <td>string</td>
                    <td>Unique category code</td>
                    <td>No (partial updates supported)</td>
                </tr>
                <tr>
                    <td>description</td>
                    <td>string</td>
                    <td>Category description</td>
                    <td>No</td>
                </tr>
                <tr>
                    <td>item_categories</td>
                    <td>array</td>
                    <td>
                        An array of item categories for bulk insert. Each item in the array should include:
                        <ul>
                            <li><code>name</code> (string, required)</li>
                            <li><code>code</code> (string, required)</li>
                            <li><code>description</code> (string, optional)</li>
                        </ul>
                    </td>
                    <td>Conditional (required for bulk updates)</td>
                </tr>
            </tbody>
        </table>

        <h3>Examples</h3>
        
        <div class="example">
            <h4>Single Update (Partial Fields)</h4>
            <pre><code>
PUT /api/item-categories?id=1
Content-Type: application/json

{
    "name": "Medical Supplies"
}
            </code></pre>
            
            <h4>Response</h4>
            <pre><code>
{
    "data": {
        "id": 1,
        "name": "Medical Supplies",
        "code": "ORIG-CODE",
        "description": "Original description"
    },
    "message": "Category updated successfully.",
    "metadata": {
        // Development metadata if enabled
    }
}
            </code></pre>
        </div>

        <div class="example">
            <h4>Bulk Update (Mixed Fields)</h4>
            <pre><code>
PUT /api/item-categories?id[]=1&id[]=2
Content-Type: application/json

{
    "item_categories": [
        {"name": "Updated Category 1"},
        {"code": "NEW-CODE-2"}
    ]
}
            </code></pre>
            
            <h4>Success Response</h4>
            <pre><code>
{
    "data": [
        {
            "id": 1,
            "name": "Updated Category 1",
            "code": "ORIG-CODE-1",
            "description": "Original description 1"
        },
        {
            "id": 2,
            "name": "Original Name 2",
            "code": "NEW-CODE-2",
            "description": "Original description 2"
        }
    ],
    "message": "Successfully updated 2 categories.",
    "metadata": {
        // Development metadata if enabled
    }
}
            </code></pre>

            <h4>Partial Success Response (With Errors)</h4>
            <pre><code>
{
    "data": [
        {
            "id": 1,
            "name": "Updated Successfully",
            "code": "CODE-1",
            "description": null
        }
    ],
    "message": "Partial update completed with errors.",
    "errors": [
        "Category with ID 2 not found."
    ],
    "metadata": {
        // Development metadata if enabled
    }
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
                    <td>422</td>
                    <td>ID parameter is required</td>
                    <td>Missing ID parameter (includes metadata in dev)</td>
                </tr>
                <tr>
                    <td>404</td>
                    <td>Category not found</td>
                    <td>Invalid ID provided (single update only)</td>
                </tr>
                <tr>
                    <td>422</td>
                    <td>Number of IDs does not match number of categories provided</td>
                    <td>Bulk update count mismatch</td>
                </tr>
                <tr>
                    <td>207</td>
                    <td>Partial update completed with errors</td>
                    <td>Bulk update with some failures (Multi-Status)</td>
                </tr>
            </tbody>
        </table>

        <h3>Notes</h3>
        <ul>
            <li>All updates are partial - only provided fields will be updated</li>
            <li>In development mode, additional metadata is included in responses</li>
            <li>For bulk updates, the order of IDs must match the order of objects in item_categories array</li>
            <li>Bulk updates will continue processing even if some items fail (returns 207 status)</li>
            <li>Empty updates (no valid fields provided) will be rejected</li>
        </ul>
    </div>

    <!-- Delete Endpoint -->
    <div class="endpoint">
        <h2>DELETE /api/item-categories</h2>
        <p>Soft delete one or more item categories (marks as deleted but retains in database).</p>

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
                    <td>integer|string|array</td>
                    <td>
                        The ID(s) of the item category(ies) to delete. Accepts multiple formats:
                        <ul>
                            <li>Single ID: <code>?id=1</code></li>
                            <li>Comma-separated: <code>?id=1,2,3</code></li>
                            <li>Array-style: <code>?id[]=1&id[]=2</code></li>
                        </ul>
                    </td>
                    <td>Conditional (required if no query)</td>
                </tr>
                <tr>
                    <td>query</td>
                    <td>object</td>
                    <td>
                        A query object to find item categories to delete (e.g., <code>{"name": "Office Supplies"}</code>).
                        Will reject if matches multiple records.
                    </td>
                    <td>Conditional (required if no id)</td>
                </tr>
            </tbody>
        </table>

        <h3>Example Requests</h3>
        <h4>Delete by single ID</h4>
        <pre>
DELETE {{ env('SERVER_DOMAIN') }}/api/item-categories?id=1
        </pre>

        <h4>Delete by multiple IDs</h4>
        <pre>
DELETE {{ env('SERVER_DOMAIN') }}/api/item-categories?id=1,2,3
        </pre>

        <h4>Delete by query</h4>
        <pre>
DELETE {{ env('SERVER_DOMAIN') }}/api/item-categories?query={"name":"Electronics"}
        </pre>

        <h3>Success Responses</h3>
        <h4>ID-based deletion</h4>
        <pre>
{
    "message": "Successfully deleted 2 record(s).",
    "deleted_ids": [1, 2]
}
        </pre>

        <h4>Query-based deletion</h4>
        <pre>
{
    "message": "Successfully deleted record.",
    "deleted_id": 3
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
                    <td>Invalid ID format.</td>
                    <td>When provided IDs are not valid numbers</td>
                </tr>
                <tr>
                    <td>404</td>
                    <td>No active records found...</td>
                    <td>When no matching active records found</td>
                </tr>
                <tr>
                    <td>409</td>
                    <td>Request would affect multiple records...</td>
                    <td>When query matches multiple records (includes data in response)</td>
                </tr>
                <tr>
                    <td>422</td>
                    <td>Invalid request.</td>
                    <td>When neither parameter is provided</td>
                </tr>
            </tbody>
        </table>

        <h3>Notes</h3>
        <ul>
            <li>This is a soft delete operation - records are marked as deleted but remain in database</li>
            <li>Only active (non-deleted) records can be deleted</li>
            <li>In development mode, additional metadata is returned for invalid requests</li>
            <li>For query operations, the system will reject requests that would affect multiple records</li>
        </ul>
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