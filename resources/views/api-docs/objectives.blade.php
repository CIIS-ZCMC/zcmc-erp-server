<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Documentation - Objectives</title>
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
        <h1>API Documentation - Objectives</h1>
        
        <div class="resource-description">
            <h2>Objectives Resource</h2>
            <p>
                The Objectives resource defines the strategic goals and purposes behind inventory management decisions,
                linking procurement activities to broader healthcare organizational objectives.
            </p>
            
            <h3>Key Features</h3>
            <ul>
                <li>Documents the purpose behind inventory decisions</li>
                <li>Aligns procurement with organizational strategy</li>
                <li>Supports justification for budget allocations</li>
                <li>Enables tracking of goal achievement</li>
                <li>Maintains historical records through soft deletion</li>
            </ul>
        </div>

        <div class="model-fields">
            <h2>Objective Model Fields</h2>
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
                        <td>code</td>
                        <td>string</td>
                        <td>Unique objective identifier</td>
                        <td>Yes</td>
                        <td>"PAT_SAFETY"</td>
                    </tr>
                    <tr>
                        <td>description</td>
                        <td>text</td>
                        <td>Detailed statement of the objective</td>
                        <td>Yes</td>
                        <td>"Ensure availability of critical medications to support patient safety initiatives"</td>
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

        <!-- Index Endpoint -->
        <div class="endpoint">
            <h2>GET /api/objectives</h2>
            <p>Retrieve objectives with options for pagination, selection mode, or fetching a single record by ID.</p>

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
                        <td>A search term to filter objectives by name.</td>
                        <td>No</td>
                    </tr>
                    <tr>
                        <td>objective_id</td>
                        <td>integer</td>
                        <td>The ID of a specific objective to retrieve.</td>
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
                    <strong>selection</strong>: Returns a flat list of objectives suitable for use in dropdowns or selection components.
                </li>
            </ul>

            <h4>Example Request for Pagination Mode</h4>
            <pre>
GET {{ env('SERVER_DOMAIN') }}/api/objectives?page=1&per_page=10&search=Ton
                <button class="copy-button" onclick="copyToClipboard('{{ env('SERVER_DOMAIN') }}/api/objectives?page=1&per_page=10&search=Ton')">
                    <i class="fas fa-copy"></i> Copy URL
                </button>
            </pre>

            <h4>Example Response for Pagination Mode</h4>
            <pre>
{
    "data": [
        {
            "id": 1,
            "description": "A user has successfully logged into the system.",
            "code": "USR_LOGINS",
            "deleted_at": null,
            "created_at": "2025-03-24T04:46:21.000000Z",
            "updated_at": "2025-03-24T04:46:21.000000Z"
        },
        {
            "id": 2,
            "description": "A user has logged out of the system.",
            "code": "USR_LOGOUTS",
            "deleted_at": null,
            "created_at": "2025-03-24T04:46:21.000000Z",
            "updated_at": "2025-03-24T04:46:21.000000Z"
        }
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
                "link": "http://http://localhost/api/objectives?per_page=10&page=1",
                "active": true
            },
            {
                "title": 2,
                "link": "http://http://localhost/api/objectives?per_page=10&page=2",
                "active": false
            },
            {
                "title": "Next",
                "link": "http://http://localhost/api/objectives?per_page=10&page=2",
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
GET {{ env('SERVER_DOMAIN') }}/api/objectives?mode=selection
                <button class="copy-button" onclick="copyToClipboard('{{ env('SERVER_DOMAIN') }}/api/objectives?mode=selection')">
                    <i class="fas fa-copy"></i> Copy URL
                </button>
            </pre>

            <h4>Example Response for Selection Mode</h4>
            <pre>
{
    "data": [
        {
            "id": 1,
            "code": "DATA_EXPORT",
            "description": "A user has exported system data."
        },
        {
            "id": 2,
            "code": "USR_LOGIN",
            "description": "A user has successfully logged into the system."
        },
        {
            "id": 3,
            "code": "USR_LOGOUT",
            "description": "A user has logged out of the system."
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
GET {{ env('SERVER_DOMAIN') }}/api/objectives?objective_id=1
                <button class="copy-button" onclick="copyToClipboard('{{ env('SERVER_DOMAIN') }}/api/objectives?objective_id=1')">
                    <i class="fas fa-copy"></i> Copy URL
                </button>
            </pre>

            <h4>Example Response for Single Record by ID</h4>
            <pre>
{
    "data": {
        "id": 1,
        "description": "A user has exported system data.",
        "code": "DATA_EXPORT",
        "deleted_at": null,
        "created_at": "2025-03-24T01:42:52.000000Z",
        "updated_at": "2025-03-24T01:42:52.000000Z"
    },
    "metadata": {
        "methods": "[GET, POST, PUT, DELETE]",
        "urls": [
            "http://localhost:8000/api/objectives?objective_id=[primary-key]",
            "http://localhost:8000/api/objectives?page={currentPage}&per_page={number_of_record_to_return}",
            "http://localhost:8000/api/objectives?page={currentPage}&per_page={number_of_record_to_return}&mode=selection",
            "http://localhost:8000/api/objectives?page={currentPage}&per_page={number_of_record_to_return}&search=value"
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
                        <td>Returned when no objective is found for the given <code>objective_id</code>.</td>
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
            <h2>POST /api/objectives</h2>
            <p>Create a new objectives or insert multiple objectives in bulk.</p>

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
                        <td>code</td>
                        <td>string</td>
                        <td>The code of the objective.</td>
                        <td>Yes (for single insert)</td>
                    </tr>
                    <tr>
                        <td>description</td>
                        <td>string</td>
                        <td>A description of the objective.</td>
                        <td>No</td>
                    </tr>
                    <tr>
                        <td>objectives</td>
                        <td>array</td>
                        <td>
                            An array of objectives for bulk insert. Each item in the array should include:
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
POST {{ env('SERVER_DOMAIN') }}/api/objectives
Content-Type: application/json

{
    "code": "DATA_EXPORTS",
    "description": "A user has exported system data.",
}
                <button class="copy-button" onclick="copyToClipboard('{{ env('SERVER_DOMAIN') }}/api/objectives')">
                    <i class="fas fa-copy"></i> Copy URL
                </button>
            </pre>

            <h3>Example Request for Bulk Insert</h3>
            <pre>
POST {{ env('SERVER_DOMAIN') }}/api/objectives
Content-Type: application/json

{
    "objectives": [
        {
            "code": "USR_LOGINS",
            "description": "A user has successfully logged into the system."
        },
        {
            "code": "USR_LOGOUTS",
            "description": "A user has logged out of the system."
        },
    ]
}

                <button class="copy-button" onclick="copyToClipboard('{{ env('SERVER_DOMAIN') }}/api/objectives')">
                    <i class="fas fa-copy"></i> Copy URL
                </button>
            </pre>

            <h3>Example Response for Single Insert</h3>
            <pre>
{
    "data": {
        "code": "DATA_EXPORTS",
        "description": "A user has exported system data.",
        "updated_at": "2025-03-24T04:48:16.000000Z",
        "created_at": "2025-03-24T04:48:16.000000Z",
        "id": 16
    },
    "message": "Successfully created objective record.",
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
            "id": 1,
            "description": "A user has successfully logged into the system.",
            "code": "USR_LOGINS",
            "deleted_at": null,
            "created_at": "2025-03-24T04:46:21.000000Z",
            "updated_at": "2025-03-24T04:46:21.000000Z"
        },
        {
            "id": 2,
            "description": "A user has logged out of the system.",
            "code": "USR_LOGOUTS",
            "deleted_at": null,
            "created_at": "2025-03-24T04:46:21.000000Z",
            "updated_at": "2025-03-24T04:46:21.000000Z"
        },
    ],
    "message": "Successfully created objectives record",
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
        <h2>PUT /api/objectives</h2>
        <p>Update one or more objectives. Supports both single and bulk updates with partial updates.</p>

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
                        The ID(s) of the objective(s) to update. Accepts multiple formats:
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
                    <td>name</td>
                    <td>string</td>
                    <td>Objective name</td>
                    <td>No (partial updates supported)</td>
                </tr>
                <tr>
                    <td>code</td>
                    <td>string</td>
                    <td>Objective code</td>
                    <td>No (partial updates supported)</td>
                </tr>
                <tr>
                    <td>description</td>
                    <td>string</td>
                    <td>Objective description</td>
                    <td>No</td>
                </tr>
                <tr>
                    <td>items</td>
                    <td>array</td>
                    <td>
                        Required for bulk updates. Array of objects containing:
                        <ul>
                            <li><strong>name</strong> (string, optional)</li>
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
PUT {{ env('SERVER_DOMAIN') }}/api/objectives?id=1
Content-Type: application/json

{
    "name": "Updated Strategic Goal",
    "code": "STRAT-2023"
}
        </pre>

        <h4>Bulk Update</h4>
        <pre>
PUT {{ env('SERVER_DOMAIN') }}/api/objectives?id[]=1&id[]=2
Content-Type: application/json

{
    "items": [
        {
            "name": "Updated Strategic Goal",
            "code": "STRAT-2023"
        },
        {
            "description": "New quarterly objective description"
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
        "name": "Updated Strategic Goal",
        "code": "STRAT-2023",
        "description": null,
        "updated_at": "2023-06-15T08:30:45.000000Z"
    },
    "message": "Objective updated successfully.",
    "metadata": {
        "methods": "[PUT]",
        "fields": ["name", "code", "description"]
    }
}
        </pre>

        <h4>Bulk Update Success</h4>
        <pre>
{
    "data": [
        {
            "id": 1,
            "name": "Updated Strategic Goal",
            "code": "STRAT-2023",
            "description": null,
            "updated_at": "2023-06-15T08:32:10.000000Z"
        },
        {
            "id": 2,
            "name": null,
            "code": null,
            "description": "New quarterly objective description",
            "updated_at": "2023-06-15T08:32:10.000000Z"
        }
    ],
    "message": "Successfully updated 2 objectives.",
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
            "name": "Updated Strategic Goal",
            "updated_at": "2023-06-15T08:32:10.000000Z"
        }
    ],
    "message": "Partial update completed with errors.",
    "errors": [
        "Objectives with ID 2 not found."
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
                    <td>Objectives not found</td>
                    <td>Invalid ID provided for single update</td>
                </tr>
                <tr>
                    <td>422</td>
                    <td>Number of IDs does not match number of objectives provided</td>
                    <td>Bulk update count mismatch</td>
                </tr>
                <tr>
                    <td>422</td>
                    <td>Multiple IDs provided but no items array for bulk update</td>
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
                    <li>Order of IDs must match order of objects in items array</li>
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
            <li><strong>Resource Formatting</strong>: Responses use ObjectiveResource for consistent output</li>
        </ul>
    </div>

    <!-- Delete Endpoint -->
    <div class="endpoint">
        <h2>DELETE /api/objectives</h2>
        <p>Soft delete one or more objectives (marks as deleted but retains in database).</p>

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
                        The ID(s) of the objective(s) to delete. Accepts multiple formats:
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
                        A query object to find objectives to delete (e.g., <code>{"name":"Strategic Goal"}</code>).
                        Will reject if matches multiple records.
                    </td>
                    <td>Conditional (required if no id)</td>
                </tr>
            </tbody>
        </table>

        <h3>Example Requests</h3>
        
        <h4>Delete by Single ID</h4>
        <pre>
DELETE {{ env('SERVER_DOMAIN') }}/api/objectives?id=1
        </pre>

        <h4>Delete by Multiple IDs</h4>
        <pre>
DELETE {{ env('SERVER_DOMAIN') }}/api/objectives?id=1,2,3
        </pre>

        <h4>Delete by Query</h4>
        <pre>
DELETE {{ env('SERVER_DOMAIN') }}/api/objectives?query={"name":"Strategic Goal"}
        </pre>

        <h3>Success Responses</h3>
        
        <h4>ID-based Deletion</h4>
        <pre>
{
    "message": "Successfully deleted 2 objective(s).",
    "deleted_ids": [1, 2],
    "count": 2
}
        </pre>

        <h4>Query-based Deletion</h4>
        <pre>
{
    "message": "Successfully deleted objective.",
    "deleted_id": 3,
    "objective_name": "Strategic Goal"
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
                    <td>Invalid objective ID format provided</td>
                    <td>When provided IDs are not valid numbers</td>
                </tr>
                <tr>
                    <td>404</td>
                    <td>No active objectives found...</td>
                    <td>When no matching active records found</td>
                </tr>
                <tr>
                    <td>409</td>
                    <td>Query matches multiple objectives...</td>
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
                    <li>Includes objective name for single deletions</li>
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