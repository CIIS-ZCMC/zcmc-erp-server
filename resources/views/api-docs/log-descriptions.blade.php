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
        <h1>API Documentation - Log Descriptions</h1>
        
        <div class="resource-description">
            <h2>Log Descriptions Resource</h2>
            <p>
                The Log Descriptions resource standardizes system activity logging by providing predefined templates
                for common inventory management events in healthcare settings.
            </p>
            
            <h3>Key Features</h3>
            <ul>
                <li>Standardized audit trail messages</li>
                <li>Consistent logging format across the system</li>
                <li>Improved searchability of log events</li>
                <li>Support for compliance reporting</li>
                <li>Soft deletion for maintaining historical templates</li>
            </ul>
        </div>

        <div class="model-fields">
            <h2>Log Description Model Fields</h2>
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
                        <td>title</td>
                        <td>string</td>
                        <td>Brief descriptive title of the log event</td>
                        <td>Yes</td>
                        <td>"Item Restocked"</td>
                    </tr>
                    <tr>
                        <td>code</td>
                        <td>string</td>
                        <td>Unique event identifier code</td>
                        <td>Yes</td>
                        <td>"ITEM_RESTOCK"</td>
                    </tr>
                    <tr>
                        <td>description</td>
                        <td>text</td>
                        <td>Template message with placeholder variables</td>
                        <td>Yes</td>
                        <td>"Item {item_name} was restocked with {quantity} {unit}"</td>
                    </tr>
                    <tr>
                        <td>deleted_at</td>
                        <td>timestamp</td>
                        <td>Soft deletion marker</td>
                        <td>No</td>
                        <td>null</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="implementation-guidelines">
            <h2>Implementation Guidelines</h2>
            <div class="guideline-card">
                <h3>Title Standards</h3>
                <ul>
                    <li>Use clear, action-oriented language</li>
                    <li>Keep under 50 characters</li>
                    <li>Follow "Noun Verb" or "Verb Noun" pattern</li>
                    <li>Avoid ambiguous terms</li>
                </ul>
            </div>
            <div class="guideline-card">
                <h3>Code Formatting</h3>
                <ul>
                    <li>Uppercase with underscore separators</li>
                    <li>Use consistent prefix for related events (e.g., "ITEM_")</li>
                    <li>Keep codes under 20 characters</li>
                    <li>Make codes self-descriptive</li>
                </ul>
            </div>
            <div class="guideline-card">
                <h3>Description Templates</h3>
                <ul>
                    <li>Use curly braces for variables {like_this}</li>
                    <li>Include all relevant context</li>
                    <li>Maintain neutral, factual tone</li>
                    <li>Keep under 120 characters when possible</li>
                </ul>
            </div>
        </div>

        <!-- Index Endpoint -->
        <div class="endpoint">
            <h2>GET /api/log-descriptions</h2>
            <p>Retrieve log descriptions with options for pagination, selection mode, or fetching a single record by ID.</p>

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
                        <td>A search term to filter item categories by title.</td>
                        <td>No</td>
                    </tr>
                    <tr>
                        <td>log_description_id</td>
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
GET {{ env('SERVER_DOMAIN') }}/api/log_descriptions?page=1&per_page=10&search=Ton
                <button class="copy-button" onclick="copyToClipboard('{{ env('SERVER_DOMAIN') }}/api/log_descriptions?page=1&per_page=10&search=Ton')">
                    <i class="fas fa-copy"></i> Copy URL
                </button>
            </pre>

            <h4>Example Response for Pagination Mode</h4>
            <pre>
{
    "data": [
        {
            "id": 3,
            "title": "User Login",
            "description": "A user has successfully logged into the system.",
            "code": "USR_LOGIN",
            "deleted_at": null,
            "created_at": "2025-03-23T17:04:28.000000Z",
            "updated_at": "2025-03-23T17:04:28.000000Z"
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
                "link": "http://localhost:8000/api/log-descriptions?search=User&per_page=10&last_initial_id=0&last_id=0",
                "is_active": true
            },
            {
                "title": "next",
                "link": "http://localhost:8000/api/log-descriptions?search=User&per_page=10&last_initial_id=0&last_id=0",
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
GET {{ env('SERVER_DOMAIN') }}/api/log_descriptions?mode=selection
                <button class="copy-button" onclick="copyToClipboard('{{ env('SERVER_DOMAIN') }}/api/log_descriptions?mode=selection')">
                    <i class="fas fa-copy"></i> Copy URL
                </button>
            </pre>

            <h4>Example Response for Selection Mode</h4>
            <pre>
{
    "data": [
        {
            "id": 1,
            "title": "Data Exported",
            "code": "DATA_EXPORT"
        },
        {
            "id": 2,
            "title": "Data Exported",
            "code": "DATA_EXPORT"
        },
        {
            "id": 3,
            "title": "User Login",
            "code": "USR_LOGIN"
        },
        {
            "id": 4,
            "title": "User Logout",
            "code": "USR_LOGOUT"
        },
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
GET {{ env('SERVER_DOMAIN') }}/api/log_descriptions?log_description_id=1
                <button class="copy-button" onclick="copyToClipboard('{{ env('SERVER_DOMAIN') }}/api/log_descriptions?log_description_id=1')">
                    <i class="fas fa-copy"></i> Copy URL
                </button>
            </pre>

            <h4>Example Response for Single Record by ID</h4>
            <pre>
{
    "data": {
        "id": 1,
        "title": "example",
        "code": "T"
    },
    "metadata": {
        "methods": "[GET, POST, PUT, DELETE]",
        "urls": [
            "{{ env('SERVER_DOMAIN') }}/api/log_descriptions?log_description_id=1",
            "{{ env('SERVER_DOMAIN') }}/api/log_descriptions?page=1&per_page=10",
            "{{ env('SERVER_DOMAIN') }}/api/log_descriptions?page=1&per_page=10&mode=selection",
            "{{ env('SERVER_DOMAIN') }}/api/log_descriptions?page=1&per_page=10&search=Ton"
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
                        <td>Returned when no item unit is found for the given <code>log_description_id</code>.</td>
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
            <h2>POST /api/log_descriptions</h2>
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
        POST {{ env('SERVER_DOMAIN') }}/api/log_descriptions
        Content-Type: application/json

        {
            "title": "Real",
            "code": "NU",
            "description": "A new item category for testing."
        }
                <button class="copy-button" onclick="copyToClipboard('{{ env('SERVER_DOMAIN') }}/api/log_descriptions')">
                    <i class="fas fa-copy"></i> Copy URL
                </button>
            </pre>

            <h3>Example Request for Bulk Insert</h3>
            <pre>
POST {{ env('SERVER_DOMAIN') }}/api/log_descriptions
Content-Type: application/json

{
    "item_categories": [
        {
            "name": "Electronics",
            "code": "ELEC",
            "description": "Devices such as mobile phones, laptops, and televisions"
        },
        {
            "name": "Furniture",
            "code": "FURN",
            "description": "Household and office furniture including tables, chairs, and cabinets"
        },
        {
            "name": "Clothing",
            "code": "CLOTH",
            "description": "Apparel including shirts, pants, dresses, and jackets"
        },
    ]
}
                <button class="copy-button" onclick="copyToClipboard('{{ env('SERVER_DOMAIN') }}/api/log_descriptions')">
                    <i class="fas fa-copy"></i> Copy URL
                </button>
            </pre>

            <h3>Example Response for Single Insert</h3>
            <pre>
{
    "data": {
        "id": 2,
        "name": "New Unit",
        "code": "NU",
        "description": "A new item category for testing."
    },
    "message": "Item category created successfully."
}
            </pre>

            <h3>Example Response for Bulk Insert</h3>
            <pre>
{
    "data": [
        {
            "id": 3,
            "name": "Electronics",
            "code": "ELEC",
            "description": "Devices such as mobile phones, laptops, and televisions"
        },
        {
            "id": 4,
            "name": "Furniture",
            "code": "FURN",
            "description": "Household and office furniture including tables, chairs, and cabinets"
        },
        {
            "id": 5,
            "name": "Clothing",
            "code": "CLOTH",
            "description": "Apparel including shirts, pants, dresses, and jackets"
        }
    ],
    "message": "Bulk item categories created successfully."
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
        <p>Update one or more log description records with partial updates. Supports both single and bulk operations.</p>

        <h3>URL Parameters</h3>
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
                        The ID(s) of the log description(s) to update. Accepts:
                        <ul>
                            <li>Single ID: <code>?id=1</code></li>
                            <li>Comma-separated: <code>?id=1,2,3</code></li>
                            <li>Array-style: <code>?id[]=1&id[]=2</code></li>
                        </ul>
                    </td>
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
                    <td>title</td>
                    <td>string</td>
                    <td>Log event title</td>
                    <td>No (partial updates supported)</td>
                </tr>
                <tr>
                    <td>code</td>
                    <td>string</td>
                    <td>Unique event code</td>
                    <td>No (partial updates supported)</td>
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
                            <li><strong>title</strong> (string, optional)</li>
                            <li><strong>code</strong> (string, optional)</li>
                            <li><strong>description</strong> (string, optional)</li>
                        </ul>
                    </td>
                    <td>Conditional (required for bulk updates)</td>
                </tr>
            </tbody>
        </table>

        <h3>Example Requests</h3>
        
        <h4>Single Update (Partial Fields)</h4>
        <pre>
PUT {{ env('SERVER_DOMAIN') }}/api/log-descriptions?id=1
Content-Type: application/json

{
    "title": "System Error",
    "code": "SYS_ERR"
}
        </pre>

        <h4>Bulk Update (Mixed Fields)</h4>
        <pre>
PUT {{ env('SERVER_DOMAIN') }}/api/log-descriptions?id[]=1&id[]=2
Content-Type: application/json

{
    "log_descriptions": [
        {
            "title": "Updated Error Log"
        },
        {
            "code": "NEW_CODE"
        }
    ]
}
        </pre>

        <h3>Example Responses</h3>
        
        <h4>Single Update Success</h4>
        <pre>
{
    "data": {
        "id": 1,
        "title": "System Error",
        "code": "SYS_ERR",
        "description": "Original description remains unchanged",
        "updated_at": "2023-06-15T08:30:45.000000Z"
    },
    "message": "Log description updated successfully.",
    "metadata": {
        "methods": "[PUT]",
        "fields": ["title", "code", "description"]
    }
}
        </pre>

        <h4>Bulk Update Success</h4>
        <pre>
{
    "data": [
        {
            "id": 1,
            "title": "Updated Error Log",
            "code": null,
            "description": null,
            "updated_at": "2023-06-15T08:32:10.000000Z"
        },
        {
            "id": 2,
            "title": null,
            "code": "NEW_CODE",
            "description": null,
            "updated_at": "2023-06-15T08:32:10.000000Z"
        }
    ],
    "message": "Successfully updated 2 log descriptions.",
    "metadata": {
        "method": "[PUT]"
    }
}
        </pre>

        <h4>Partial Update (With Errors)</h4>
        <pre>
{
    "data": [
        {
            "id": 1,
            "title": "Updated Error Log",
            "updated_at": "2023-06-15T08:32:10.000000Z"
        }
    ],
    "message": "Partial update completed with errors.",
    "errors": [
        "Log description with ID 2 not found."
    ],
    "metadata": {
        "method": "[PUT]"
    }
}
        </pre>

        <h3>Error Responses</h3>
        <table>
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
                    <td>Log description not found</td>
                    <td>Invalid ID provided for single update</td>
                </tr>
                <tr>
                    <td>422</td>
                    <td>Number of IDs does not match number of log descriptions</td>
                    <td>Bulk update count mismatch</td>
                </tr>
                <tr>
                    <td>422</td>
                    <td>Multiple IDs provided but no log_descriptions array</td>
                    <td>Multiple IDs without bulk data</td>
                </tr>
                <tr>
                    <td>207</td>
                    <td>Partial update completed with errors</td>
                    <td>Bulk update with some failures (Multi-Status)</td>
                </tr>
            </tbody>
        </table>

        <h3>Implementation Notes</h3>
        <ul>
            <li><strong>Partial Updates</strong>: Only provided fields will be updated</li>
            <li><strong>Bulk Processing</strong>:
                <ul>
                    <li>Order of IDs must match order of objects in log_descriptions array</li>
                    <li>Continues processing even if some items fail</li>
                </ul>
            </li>
            <li><strong>Validation</strong>:
                <ul>
                    <li>At least one field must be provided for each update</li>
                    <li>Empty updates will be rejected</li>
                </ul>
            </li>
            <li><strong>Development Mode</strong>: Additional metadata included for invalid requests</li>
        </ul>
    </div>

    <!-- Delete Endpoint -->
    <div class="endpoint">
        <h2>DELETE /api/log_descriptions</h2>
        <p>Soft delete one or more log descriptions (marks as deleted but retains in database).</p>

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
                        The ID(s) of the log description(s) to delete. Accepts multiple formats:
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
                        A query object to find log descriptions to delete (e.g., <code>{"code":"LOG001"}</code>).
                        Will reject if matches multiple records.
                    </td>
                    <td>Conditional (required if no id)</td>
                </tr>
            </tbody>
        </table>

        <h3>Example Requests</h3>
        
        <h4>Delete by Single ID</h4>
        <pre>
DELETE {{ env('SERVER_DOMAIN') }}/api/log_descriptions?id=1
        </pre>

        <h4>Delete by Multiple IDs</h4>
        <pre>
DELETE {{ env('SERVER_DOMAIN') }}/api/log_descriptions?id=1,2,3
        </pre>

        <h4>Delete by Query</h4>
        <pre>
DELETE {{ env('SERVER_DOMAIN') }}/api/log_descriptions?query={"code":"LOG001"}
        </pre>

        <h3>Success Responses</h3>
        
        <h4>ID-based Deletion</h4>
        <pre>
{
    "message": "Successfully deleted 2 log description(s).",
    "deleted_ids": [1, 2],
    "count": 2
}
        </pre>

        <h4>Query-based Deletion</h4>
        <pre>
{
    "message": "Successfully deleted log description.",
    "deleted_id": 3,
    "description": "System maintenance log entry"
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
                    <td>Invalid log description ID format provided</td>
                    <td>When provided IDs are not valid numbers</td>
                </tr>
                <tr>
                    <td>404</td>
                    <td>No active log descriptions found...</td>
                    <td>When no matching active records found</td>
                </tr>
                <tr>
                    <td>409</td>
                    <td>Query matches multiple log descriptions...</td>
                    <td>When query matches multiple records (includes data in response)</td>
                </tr>
                <tr>
                    <td>422</td>
                    <td>Invalid request</td>
                    <td>When neither parameter is provided (includes metadata in dev)</td>
                </tr>
            </tbody>
        </table>

        <h3>Implementation Notes</h3>
        <ul>
            <li><strong>Soft Delete</strong>: Records are marked as deleted (sets deleted_at timestamp) but remain in database</li>
            <li><strong>Active Records Only</strong>: Only affects non-deleted records (where deleted_at is null)</li>
            <li><strong>ID Processing</strong>:
                <ul>
                    <li>Handles single ID, comma-separated list, and array formats</li>
                    <li>Validates all IDs are positive integers</li>
                    <li>Only processes records that actually exist</li>
                </ul>
            </li>
            <li><strong>Query Safety</strong>:
                <ul>
                    <li>Rejects queries that would affect multiple records</li>
                    <li>Provides helpful suggestions in conflict responses</li>
                </ul>
            </li>
            <li><strong>Response Details</strong>:
                <ul>
                    <li>Returns count of deleted records</li>
                    <li>Includes IDs of successfully deleted records</li>
                    <li>Optionally includes description text for single deletions</li>
                </ul>
            </li>
            <li><strong>Development Mode</strong>: Provides additional metadata when invalid requests are made</li>
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