<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Documentation - Item Classifications</title>
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
        <h1>API Documentation - Item Classifications</h1>

        <!-- Index Endpoint -->
        <div class="endpoint">
            <h2>GET /api/item-classifications</h2>
            <p>Retrieve item classifications with options for pagination, selection mode, or fetching a single record by ID.</p>

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
                        <td>A search term to filter item classifications by name.</td>
                        <td>No</td>
                    </tr>
                    <tr>
                        <td>item_classification_id</td>
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
                    <strong>selection</strong>: Returns a flat list of item classifications suitable for use in dropdowns or selection components.
                </li>
            </ul>

            <h4>Example Request for Pagination Mode</h4>
            <pre>
GET {{ env('SERVER_DOMAIN') }}/api/item-classifications?page=1&per_page=10&search=Ton
                <button class="copy-button" onclick="copyToClipboard('{{ env('SERVER_DOMAIN') }}/api/item-classifications?page=1&per_page=10&search=Ton')">
                    <i class="fas fa-copy"></i> Copy URL
                </button>
            </pre>

            <h4>Example Response for Pagination Mode</h4>
            <pre>
{
    "data": [
        {
            "id": 6,
            "item_category_id": 1,
            "name": "Pharmaceuticals",
            "code": "ph",
            "description": "Medicines and drugs used for treatment",
            "deleted_at": null,
            "created_at": "2025-03-24T06:02:44.000000Z",
            "updated_at": "2025-03-24T06:02:44.000000Z"
        },
        {
            "id": 7,
            "item_category_id": 1,
            "name": "Hospital Furniture",
            "code": "hf",
            "description": "Beds, stretchers, and other patient care furniture",
            "deleted_at": null,
            "created_at": "2025-03-24T06:02:44.000000Z",
            "updated_at": "2025-03-24T06:02:44.000000Z"
        },
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
                "link": "http://http://localhost/api/item_classifications?per_page=10&page=1",
                "active": true
            },
            {
                "title": 2,
                "link": "http://http://localhost/api/item_classifications?per_page=10&page=2",
                "active": false
            },
            {
                "title": "Next",
                "link": "http://http://localhost/api/item_classifications?per_page=10&page=2",
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
GET {{ env('SERVER_DOMAIN') }}/api/item-classifications?mode=selection
                <button class="copy-button" onclick="copyToClipboard('{{ env('SERVER_DOMAIN') }}/api/item-classifications?mode=selection')">
                    <i class="fas fa-copy"></i> Copy URL
                </button>
            </pre>

            <h4>Example Response for Selection Mode</h4>
            <pre>
{
    "data": [
        {
            "id": 6,
            "code": "ph",
            "description": "Medicines and drugs used for treatment"
        },
        {
            "id": 7,
            "code": "hf",
            "description": "Beds, stretchers, and other patient care furniture"
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
GET {{ env('SERVER_DOMAIN') }}/api/item-classifications?item_classification_id=1
                <button class="copy-button" onclick="copyToClipboard('{{ env('SERVER_DOMAIN') }}/api/item-classifications?item_classification_id=1')">
                    <i class="fas fa-copy"></i> Copy URL
                </button>
            </pre>

            <h4>Example Response for Single Record by ID</h4>
            <pre>
{
    "data": {
        "id": 1,
        "item_category_id": 1,
        "name": "Hospital Supplies",
        "code": "hs",
        "description": "Classification of items used in hospital operations",
        "deleted_at": "2025-03-24 06:06:25",
        "created_at": "2025-03-24T06:02:12.000000Z",
        "updated_at": "2025-03-24T06:06:25.000000Z"
    },
    "metadata": {
        "methods": "[GET, POST, PUT, DELETE]",
        "urls": [
            "http://localhost:8000/api/item_classifications?item_classification_id=[primary-key]",
            "http://localhost:8000/api/item_classifications?page={currentPage}&per_page={number_of_record_to_return}",
            "http://localhost:8000/api/item_classifications?page={currentPage}&per_page={number_of_record_to_return}&mode=selection",
            "http://localhost:8000/api/item_classifications?page={currentPage}&per_page={number_of_record_to_return}&search=value"
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
                        <td>Returned when no item unit is found for the given <code>item_classification_id</code>.</td>
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
            <h2>POST /api/item-classifications</h2>
            <p>Create a new item unit or insert multiple item classifications in bulk.</p>

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
                        <td>The name of the item classification.</td>
                        <td>Yes (for single insert)</td>
                    </tr>
                    <tr>
                        <td>code</td>
                        <td>string</td>
                        <td>The code of the item classification.</td>
                        <td>Yes (for single insert)</td>
                    </tr>
                    <tr>
                        <td>description</td>
                        <td>string</td>
                        <td>A description of the item classification.</td>
                        <td>No</td>
                    </tr>
                    <tr>
                        <td>category_id</td>
                        <td>string</td>
                        <td>A category the item class is under.</td>
                        <td>No</td>
                    </tr>
                    <tr>
                        <td>item_units</td>
                        <td>array</td>
                        <td>
                            An array of item classifications for bulk insert. Each item in the array should include:
                            <ul>
                                <li><code>name</code> (string, required)</li>
                                <li><code>code</code> (string, required)</li>
                                <li><code>description</code> (string, optional)</li>
                                <li><code>category_id</code> (unsignedBigInteger)</li>
                            </ul>
                        </td>
                        <td>Yes (for bulk insert)</td>
                    </tr>
                </tbody>
            </table>

            <h3>Example Request for Single Insert</h3>
            <pre>
POST {{ env('SERVER_DOMAIN') }}/api/item-classifications
Content-Type: application/json

{
    "name": "Hospital Supplies",
    "code": "hs",
    "description": "Classification of items used in hospital operations",
    "item_category_id": 1,
}
                <button class="copy-button" onclick="copyToClipboard('{{ env('SERVER_DOMAIN') }}/api/item-classifications')">
                    <i class="fas fa-copy"></i> Copy URL
                </button>
            </pre>

            <h3>Example Request for Bulk Insert</h3>
            <pre>
POST {{ env('SERVER_DOMAIN') }}/api/item-classifications
Content-Type: application/json

{
    "item_classifications": [
        {
            "name": "Medical Equipment",
            "code": "me",
            "description": "Devices and tools used for patient care",
            "item_category_id": 1
        },
        {
            "name": "Surgical Instruments",
            "code": "si",
            "description": "Tools used during surgical procedures",
            "item_category_id": 1
        },
    ]
}
                <button class="copy-button" onclick="copyToClipboard('{{ env('SERVER_DOMAIN') }}/api/item-classifications')">
                    <i class="fas fa-copy"></i> Copy URL
                </button>
            </pre>

            <h3>Example Response for Single Insert</h3>
            <pre>
{
    "data": {
        "name": "Hospital Supplies",
        "code": "hsr",
        "description": "Classification of items used in hospital operations",
        "item_category_id": "1",
        "updated_at": "2025-03-24T06:12:46.000000Z",
        "created_at": "2025-03-24T06:12:46.000000Z",
        "id": 15
    },
    "message": "Successfully created item_classifications record.",
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
            "id": 16,
            "item_category_id": 1,
            "name": "Medical Equipments",
            "code": "mes",
            "description": "Devices and tools used for patient care",
            "deleted_at": null,
            "created_at": "2025-03-24T06:13:15.000000Z",
            "updated_at": "2025-03-24T06:13:15.000000Z"
        },
        {
            "id": 17,
            "item_category_id": 1,
            "name": "Surgical Instruments",
            "code": "sis",
            "description": "Tools used during surgical procedures",
            "deleted_at": null,
            "created_at": "2025-03-24T06:13:15.000000Z",
            "updated_at": "2025-03-24T06:13:15.000000Z"
        },
    ],
    "message": "Successfully created item_classificationss record",
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
        <h2>PUT /api/item-classifications</h2>
        <p>Update an existing item unit.</p>

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
                    <td>integer</td>
                    <td>The ID of the item unit to update.</td>
                    <td>Yes (if <code>query</code> is not provided)</td>
                </tr>
                <tr>
                    <td>query</td>
                    <td>object</td>
                    <td>A query object to find the item unit to update (e.g., <code>{"code": "example"}</code>).</td>
                    <td>Yes (if <code>id</code> is not provided)</td>
                </tr>
                <tr>
                    <td>name</td>
                    <td>string</td>
                    <td>The updated name of the item unit.</td>
                    <td>No</td>
                </tr>
                <tr>
                    <td>code</td>
                    <td>string</td>
                    <td>The updated code of the item unit.</td>
                    <td>No</td>
                </tr>
                <tr>
                    <td>description</td>
                    <td>string</td>
                    <td>The updated description of the item unit.</td>
                    <td>No</td>
                </tr>
            </tbody>
        </table>

        <h3>Example Request</h3>
        <pre>
PUT {{ env('SERVER_DOMAIN') }}/api/item-classifications?id=1
Content-Type: application/json

{
    "name": "Medical Equipment PASSED",
    "code": "meh",
    "description": "Devices and tools used for patient care",
    "item_category_id": 1
}
        <button class="copy-button" onclick="copyToClipboard('{{ env('SERVER_DOMAIN') }}/api/item-classifications?id=1')">
            <i class="fas fa-copy"></i> Copy URL
        </button>
    </pre>

    <h3>Example Response</h3>
    <pre>
{
    "data": {
        "id": 8,
        "item_category_id": 1,
        "name": "Medical Equipment PASSED",
        "code": "meh",
        "description": "Devices and tools used for patient care",
        "deleted_at": null,
        "created_at": "2025-03-24T06:02:44.000000Z",
        "updated_at": "2025-03-24T06:14:44.000000Z"
    },
    "metadata": {
        "methods": "[GET, PUT, DELETE]",
        "formats": [
            "http://localhost:8000/api/item_classifications?id=1",
            "http://localhost:8000/api/item_classificationsquery[target_field]=value"
        ],
        "fields": [
            "code"
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
                    <td>Invalid request.</td>
                    <td>Returned when neither <code>id</code> nor <code>query</code> is provided.</td>
                </tr>
                <tr>
                    <td>404</td>
                    <td>No record found.</td>
                    <td>Returned when no item unit is found for the given <code>id</code> or <code>query</code>.</td>
                </tr>
                <tr>
                    <td>409</td>
                    <td>Request has multiple records.</td>
                    <td>Returned when the <code>query</code> matches multiple records.</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Delete Endpoint -->
    <div class="endpoint">
        <h2>DELETE /api/item-classifications</h2>
        <p>Delete one or more item classifications.</p>

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
DELETE {{ env('SERVER_DOMAIN') }}/api/item-classifications?id=1
        <button class="copy-button" onclick="copyToClipboard('{{ env('SERVER_DOMAIN') }}/api/item-classifications?item_classification_id=1')">
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
                    <td>Returned when no item classifications are found for the given <code>id</code> or <code>query</code>.</td>
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