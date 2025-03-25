<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Documentation - Type Of Functions</title>
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
        <h1>API Documentation - Type Of Functions</h1>
        
        <div class="resource-description">
            <h2>Type of Functions Resource</h2>
            <p>
                The Type of Functions resource categorizes the various operational functions within healthcare
                inventory management, helping to organize and track activities related to procurement, storage,
                and distribution of medical supplies.
            </p>
            
            <h3>Key Features</h3>
            <ul>
                <li>Standardized classification of inventory-related functions</li>
                <li>Supports workflow organization and reporting</li>
                <li>Enables function-based access control</li>
                <li>Soft deletion for maintaining historical records</li>
            </ul>
        </div>

        <div class="model-fields">
            <h2>Type of Functions Model Fields</h2>
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
                        <td>type</td>
                        <td>string</td>
                        <td>Name of the function type</td>
                        <td>Yes</td>
                        <td>"Procurement"</td>
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

        <div class="usage-notes">
            <h2>Implementation Notes</h2>
            <ul>
                <li>Function types should be broad enough to cover multiple related activities</li>
                <li>Maintain consistent naming conventions across all functions</li>
                <li>Review function types annually for relevance</li>
                <li>Use soft delete rather than hard delete for historical reporting</li>
            </ul>
        </div>

        <!-- Index Endpoint -->
        <div class="endpoint">
            <h2>GET /api/type-of-functions</h2>
            <p>Retrieve type of functions with options for pagination, selection mode, or fetching a single record by ID.</p>

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
                        <td>A search term to filter type of functions by name.</td>
                        <td>No</td>
                    </tr>
                    <tr>
                        <td>type_of_function_id</td>
                        <td>integer</td>
                        <td>The ID of a specific type of function to retrieve.</td>
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
                    <strong>selection</strong>: Returns a flat list of type of functions suitable for use in dropdowns or selection components.
                </li>
            </ul>

            <h4>Example Request for Pagination Mode</h4>
            <pre>
GET {{ env('SERVER_DOMAIN') }}/api/type-of-functions?page=1&per_page=10&search=Ton
                <button class="copy-button" onclick="copyToClipboard('{{ env('SERVER_DOMAIN') }}/api/type-of-functions?page=1&per_page=10&search=Ton')">
                    <i class="fas fa-copy"></i> Copy URL
                </button>
            </pre>

            <h4>Example Response for Pagination Mode</h4>
            <pre>
{
    "data": [
        {
            "id": 3,
            "type": "USR_LOGINS",
            "deleted_at": null,
            "created_at": "2025-03-24T02:41:52.000000Z",
            "updated_at": "2025-03-24T02:41:52.000000Z"
        },
        {
            "id": 4,
            "type": "USR_LOGOUTS",
            "deleted_at": null,
            "created_at": "2025-03-24T02:41:52.000000Z",
            "updated_at": "2025-03-24T02:41:52.000000Z"
        },
        {
            "id": 7,
            "type": "USR_LOGOUTR",
            "deleted_at": null,
            "created_at": "2025-03-24T02:54:26.000000Z",
            "updated_at": "2025-03-24T02:54:26.000000Z"
        }
    ],
    "metadata": {
        "methods": "[GET,POST,PUT,DELETE]",
        "pagination": [
            {
                "title": "previous",
                "link": null,
                "is_active": false
            },
            {
                "title": 1,
                "link": "http://localhost:8000/api/type-of-functions?search=USR&per_page=10&last_initial_id=0&last_id=0",
                "is_active": true
            },
            {
                "title": "next",
                "link": "http://localhost:8000/api/type-of-functions?search=USR&per_page=10&last_initial_id=0&last_id=0",
                "is_active": true
            }
        ],
        "page": "1",
        "total_page": 1
    }
}
            </pre>

            <h4>Example Request for Selection Mode</h4>
            <pre>
GET {{ env('SERVER_DOMAIN') }}/api/type-of-functions?mode=selection
                <button class="copy-button" onclick="copyToClipboard('{{ env('SERVER_DOMAIN') }}/api/type-of-functions?mode=selection')">
                    <i class="fas fa-copy"></i> Copy URL
                </button>
            </pre>

            <h4>Example Response for Selection Mode</h4>
            <pre>
{
    "data": [
        {
            "id": 1,
            "type": "DATA_EXPORTS"
        },
        {
            "id": 2,
            "type": "DATA_EXPORTS"
        },
        {
            "id": 3,
            "type": "USR_LOGINS"
        },
        {
            "id": 4,
            "type": "USR_LOGOUTS"
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
GET {{ env('SERVER_DOMAIN') }}/api/type-of-functions?type_of_function_id=1
                <button class="copy-button" onclick="copyToClipboard('{{ env('SERVER_DOMAIN') }}/api/type-of-functions?type_of_function_id=1')">
                    <i class="fas fa-copy"></i> Copy URL
                </button>
            </pre>

            <h4>Example Response for Single Record by ID</h4>
            <pre>
{
    "data": {
        "id": 1,
        "type": "DATA_EXPORTS",
        "deleted_at": null,
        "created_at": "2025-03-24T02:40:05.000000Z",
        "updated_at": "2025-03-24T02:40:05.000000Z"
    },
    "metadata": {
        "methods": "[GET, POST, PUT, DELETE]",
        "urls": [
            "http://localhost:8000/api/type-of-functions?type_of_function_id=[primary-key]",
            "http://localhost:8000/api/type-of-functions?page={currentPage}&per_page={number_of_record_to_return}",
            "http://localhost:8000/api/type-of-functions?page={currentPage}&per_page={number_of_record_to_return}&mode=selection",
            "http://localhost:8000/api/type-of-functions?page={currentPage}&per_page={number_of_record_to_return}&search=value"
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
                        <td>Returned when no type of function is found for the given <code>type_of_function_id</code>.</td>
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
            <h2>POST /api/type-of-functions</h2>
            <p>Create a new type of functions or insert multiple type of functions in bulk.</p>

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
                        <td>type</td>
                        <td>string</td>
                        <td>The code of the type of function.</td>
                        <td>Yes (for single insert)</td>
                    </tr>
                    <tr>
                        <td>types_of_functions</td>
                        <td>array</td>
                        <td>
                            An array of type of functions for bulk insert. Each item in the array should include:
                            <ul>
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
POST {{ env('SERVER_DOMAIN') }}/api/type-of-functions
Content-Type: application/json

{
    "type": "DATA_EXPORTS",
}
                <button class="copy-button" onclick="copyToClipboard('{{ env('SERVER_DOMAIN') }}/api/type-of-functions')">
                    <i class="fas fa-copy"></i> Copy URL
                </button>
            </pre>

            <h3>Example Request for Bulk Insert</h3>
            <pre>
POST {{ env('SERVER_DOMAIN') }}/api/type-of-functions
Content-Type: application/json

{
    "types_of_functions": [
        {
            "type": "USR_LOGINS"
        },
        {
            "type": "USR_LOGOUTS"
        },
        {
            "type": "LOGIN_FAILS"
        },
        {
            "type": "PWD_CHANGES"
        }
    ]
}
                <button class="copy-button" onclick="copyToClipboard('{{ env('SERVER_DOMAIN') }}/api/type-of-functions')">
                    <i class="fas fa-copy"></i> Copy URL
                </button>
            </pre>

            <h3>Example Response for Single Insert</h3>
            <pre>
{
    "data": {
        "type": "DATA_EXPORTS",
        "updated_at": "2025-03-24T02:40:05.000000Z",
        "created_at": "2025-03-24T02:40:05.000000Z",
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
            "id": 7,
            "type": "USR_LOGOUTR",
            "deleted_at": null,
            "created_at": "2025-03-24T02:54:26.000000Z",
            "updated_at": "2025-03-24T02:54:26.000000Z"
        },
        {
            "id": 8,
            "type": "LOGIN_FAILR",
            "deleted_at": null,
            "created_at": "2025-03-24T02:54:26.000000Z",
            "updated_at": "2025-03-24T02:54:26.000000Z"
        },
        {
            "id": 9,
            "type": "PWD_CHANGER",
            "deleted_at": null,
            "created_at": "2025-03-24T02:54:26.000000Z",
            "updated_at": "2025-03-24T02:54:26.000000Z"
        }
    ],
    "message": "Successfully created item categorys record",
    "metadata": {
        "methods": "[GET, POST, PUT ,DELETE]",
        "duplicate_items": [
            {
                "id": 3,
                "type": "USR_LOGINS",
                "created_at": "2025-03-24T02:41:52.000000Z",
                "updated_at": "2025-03-24T02:41:52.000000Z"
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
        <h2>PUT /api/type-of-functions</h2>
        <p>Update one or more type of function records. Supports both single and bulk updates.</p>

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
                    <td>
                        The ID(s) of the type of function(s) to update. Can be:<br>
                        - Single ID (e.g., <code>1</code>)<br>
                        - Comma-separated list (e.g., <code>1,2,3</code>)<br>
                        - Array format (e.g., <code>id[]=1&id[]=2</code>)
                    </td>
                    <td>Yes</td>
                </tr>
                <tr>
                    <td>type_of_functions</td>
                    <td>array</td>
                    <td>
                        Required for bulk updates. Array of objects containing update data, where each object corresponds to an ID in the same order.<br>
                        Example: <code>[{"type": "Type1"}, {"type": "Type2"}]</code>
                    </td>
                    <td>Yes (for bulk updates)</td>
                </tr>
                <tr>
                    <td>type</td>
                    <td>string</td>
                    <td>
                        The type value to update (for single updates)<br>
                        Data is automatically cleaned/sanitized before updating.
                    </td>
                    <td>Yes (for single updates)</td>
                </tr>
            </tbody>
        </table>

        <h3>Example Requests</h3>
        
        <h4>Single Update</h4>
        <pre>
PUT {{ env('SERVER_DOMAIN') }}/api/type-of-functions?id=1
Content-Type: application/json

{
    "type": "USR_LOGIN_UPDATED"
}
            <button class="copy-button" onclick="copyToClipboard('PUT {{ env('SERVER_DOMAIN') }}/api/type-of-functions?id=1')">
                <i class="fas fa-copy"></i> Copy
            </button>
        </pre>

        <h4>Bulk Update</h4>
        <pre>
PUT {{ env('SERVER_DOMAIN') }}/api/type-of-functions?id=1,2,3
Content-Type: application/json

{
    "type_of_functions": [
        {"type": "Type1"},
        {"type": "Type2"},
        {"type": "Type3"}
    ]
}
            <button class="copy-button" onclick="copyToClipboard('PUT {{ env('SERVER_DOMAIN') }}/api/type-of-functions?id=1,2,3')">
                <i class="fas fa-copy"></i> Copy
            </button>
        </pre>

        <h3>Example Responses</h3>
        
        <h4>Successful Single Update</h4>
        <pre>
{
    "data": {
        "id": 1,
        "type": "USR_LOGIN_UPDATED",
        "deleted_at": null,
        "created_at": "2025-03-24T02:41:52.000000Z",
        "updated_at": "2025-03-24T03:01:58.000000Z"
    },
    "message": "Type of Function updated successfully.",
    "metadata": {
        "methods": ["PUT"],
        "required_fields": ["type"]
    }
}
        </pre>

        <h4>Successful Bulk Update</h4>
        <pre>
{
    "data": [
        {
            "id": 1,
            "type": "Type1",
            "deleted_at": null,
            "created_at": "2025-03-24T02:41:52.000000Z",
            "updated_at": "2025-03-24T03:01:58.000000Z"
        },
        {
            "id": 2,
            "type": "Type2",
            "deleted_at": null,
            "created_at": "2025-03-24T02:41:52.000000Z",
            "updated_at": "2025-03-24T03:01:58.000000Z"
        }
    ],
    "message": "Successfully updated 2 type of functions.",
    "metadata": {
        "methods": ["GET", "POST", "PUT", "DELETE"]
    }
}
        </pre>

        <h4>Partial Bulk Update (with errors)</h4>
        <pre>
{
    "data": [
        {
            "id": 1,
            "type": "Type1",
            "deleted_at": null,
            "created_at": "2025-03-24T02:41:52.000000Z",
            "updated_at": "2025-03-24T03:01:58.000000Z"
        }
    ],
    "message": "Partial update completed with errors.",
    "metadata": {
        "method": "[PUT]",
        "errors": [
            "TypeOfFunction with ID 2 not found."
        ]
    }
}
        </pre>

        <h3>Error Responses</h3>
        <table>
            <thead>
                <tr>
                    <th>Status Code</th>
                    <th>Response</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>422</td>
                    <td><pre>{
        "message": "ID parameter is required.",
        "metadata": { ... }
    }</pre></td>
                    <td>Returned when no ID parameter is provided.</td>
                </tr>
                <tr>
                    <td>422</td>
                    <td><pre>{
        "message": "Number of IDs does not match number of type of functions provided.",
        "metadata": { ... }
    }</pre></td>
                    <td>Returned in bulk updates when IDs count doesn't match type_of_functions array length.</td>
                </tr>
                <tr>
                    <td>422</td>
                    <td><pre>{
        "message": "Multiple IDs provided but no type of functions array for bulk update."
    }</pre></td>
                    <td>Returned when multiple IDs are provided without bulk update data.</td>
                </tr>
                <tr>
                    <td>404</td>
                    <td><pre>{
        "message": "Type of function not found."
    }</pre></td>
                    <td>Returned when the specified ID doesn't exist (single update).</td>
                </tr>
                <tr>
                    <td>207</td>
                    <td><pre>{
        "data": [...],
        "message": "Partial update completed with errors.",
        "metadata": {
            "errors": [...]
        }
    }</pre></td>
                    <td>Returned for bulk updates when some items fail to update.</td>
                </tr>
            </tbody>
        </table>

        <h3>Notes</h3>
        <ul>
            <li>For bulk updates, the order of IDs must match the order of objects in the type_of_functions array</li>
            <li>All update data is automatically cleaned/sanitized before being applied</li>
            <li>The 'type' field is required for updates</li>
            <li>In development environment, responses include additional metadata</li>
            <li>Partial updates (207 status) include successfully updated items in the response along with error messages</li>
            <li>The endpoint does not support query-based updates - only ID-based updates are allowed</li>
        </ul>
    </div>

    <!-- Delete Endpoint -->
    <div class="endpoint">
        <h2>DELETE /api/type-of-functions</h2>
        <p>Delete one or more type of function records (soft delete).</p>

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
                    <td>integer, string, or array</td>
                    <td>
                        The ID(s) of the function type(s) to delete. Can be:<br>
                        - Single ID (e.g., <code>1</code>)<br>
                        - Comma-separated list (e.g., <code>1,2,3</code>)<br>
                        - Array format (e.g., <code>id[]=1&id[]=2</code>)<br>
                        Only active (non-deleted) records will be affected.
                    </td>
                    <td>Yes (if <code>query</code> is not provided)</td>
                </tr>
                <tr>
                    <td>query</td>
                    <td>object</td>
                    <td>
                        A query object to find the function type(s) to delete (e.g., <code>{"type": "example"}</code>).<br>
                        If the query matches multiple records, the operation will fail with a 409 Conflict response.
                    </td>
                    <td>Yes (if <code>id</code> is not provided)</td>
                </tr>
            </tbody>
        </table>

        <h3>Example Requests</h3>
        <h4>Delete by single ID</h4>
        <pre>
DELETE {{ env('SERVER_DOMAIN') }}/api/type-of-functions?id=1
            <button class="copy-button" onclick="copyToClipboard('{{ env('SERVER_DOMAIN') }}/api/type-of-functions?id=1')">
                <i class="fas fa-copy"></i> <span>Copy URL</span>
            </button>
        </pre>

        <h4>Delete by multiple IDs</h4>
        <pre>
DELETE {{ env('SERVER_DOMAIN') }}/api/type-of-functions?id=1,2,3
            <button class="copy-button" onclick="copyToClipboard('{{ env('SERVER_DOMAIN') }}/api/type-of-functions?id=1,2,3')">
                <i class="fas fa-copy"></i> <span>Copy URL</span>
            </button>
        </pre>

        <h4>Delete by query</h4>
        <pre>
DELETE {{ env('SERVER_DOMAIN') }}/api/type-of-functions?query={"type":"example"}
            <button class="copy-button" onclick="copyToClipboard('{{ env('SERVER_DOMAIN') }}/api/type-of-functions?query={"type":"example"}')">
                <i class="fas fa-copy"></i> <span>Copy URL</span>
            </button>
        </pre>

        <h3>Example Responses</h3>
        <h4>Successful deletion (by ID)</h4>
        <pre>
{
    "message": "Successfully deleted 3 function type(s).",
    "deleted_ids": [1, 2, 3],
    "count": 3
}
        </pre>

        <h4>Successful deletion (by query)</h4>
        <pre>
{
    "message": "Successfully deleted function type.",
    "deleted_id": 1,
    "function_name": "Example Function Type"
}
        </pre>

        <h4>Multiple records found (query)</h4>
        <pre>
{
    "data": [
        {
            "id": 1,
            "type": "example",
            "name": "Function Type 1"
        },
        {
            "id": 2,
            "type": "example",
            "name": "Function Type 2"
        }
    ],
    "message": "Query matches multiple function types.",
    "suggestion": "Use ID parameter for precise deletion or add more query criteria"
}
        </pre>

        <h3>Error Responses</h3>
        <table>
            <thead>
                <tr>
                    <th>Status Code</th>
                    <th>Response Body</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>400</td>
                    <td><pre>{"message": "Invalid function type ID format provided."}</pre></td>
                    <td>Returned when ID parameter contains non-numeric or invalid values.</td>
                </tr>
                <tr>
                    <td>404</td>
                    <td><pre>{"message": "No active function types found with the provided IDs."}</pre>
    <pre>{"message": "No active function type found matching your criteria."}</pre></td>
                    <td>Returned when no active (non-deleted) function types are found.</td>
                </tr>
                <tr>
                    <td>409</td>
                    <td><pre>{
        "data": [...],
        "message": "Query matches multiple function types.",
        "suggestion": "Use ID parameter for precise deletion or add more query criteria"
    }</pre></td>
                    <td>Returned when query parameter matches multiple records.</td>
                </tr>
                <tr>
                    <td>422</td>
                    <td><pre>{
        "message": "No parameters found for deletion.",
        "metadata": {...},
        "hint": "Provide either 'id' or 'query' parameter"
    }</pre></td>
                    <td>Returned when neither id nor query parameter is provided.</td>
                </tr>
            </tbody>
        </table>

        <h3>Notes</h3>
        <ul>
            <li>This endpoint performs a soft delete (sets deleted_at timestamp) rather than permanent deletion.</li>
            <li>Only active records (where deleted_at is null) will be affected by the operation.</li>
            <li>When using the query parameter, it must match exactly one record for the operation to succeed.</li>
            <li>In development environment, error responses include additional metadata and hints.</li>
            <li>For bulk deletions, all IDs must reference existing, active records for the operation to succeed.</li>
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