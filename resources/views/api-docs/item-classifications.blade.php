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

            .classification-hierarchy {
                grid-template-columns: 1fr;
            }
            
            .resource-description,
            .model-fields,
            .implementation-standards {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="{{ url('/api-docs') }}" class="back-link">← Back to API Documentation</a>
        <h1>API Documentation - Item Classifications</h1>

        <div class="resource-description">
            <h2>Item Classifications Resource</h2>
            <p>
                Item Classifications provide a detailed categorization system within each Item Category,
                enabling precise inventory management and specialized reporting for healthcare organizations.
            </p>
            
            <h3>Key Features</h3>
            <ul>
                <li>Hierarchical organization under parent categories</li>
                <li>Granular inventory tracking and reporting</li>
                <li>Standardized taxonomy for procurement</li>
                <li>Support for clinical and administrative classification</li>
                <li>Soft deletion for historical reference</li>
            </ul>
        </div>

        <div class="model-fields">
            <h2>Item Classification Model Fields</h2>
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
                        <td>item_category_id</td>
                        <td>integer</td>
                        <td>Reference to parent category</td>
                        <td>Yes</td>
                        <td>3</td>
                    </tr>
                    <tr>
                        <td>name</td>
                        <td>string</td>
                        <td>Descriptive classification name</td>
                        <td>Yes</td>
                        <td>"Diagnostic Imaging"</td>
                    </tr>
                    <tr>
                        <td>code</td>
                        <td>string</td>
                        <td>Unique classification code</td>
                        <td>Yes</td>
                        <td>"DIAG-IMG"</td>
                    </tr>
                    <tr>
                        <td>description</td>
                        <td>text</td>
                        <td>Detailed scope definition</td>
                        <td>No</td>
                        <td>"Includes all radiology and imaging equipment"</td>
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
    "name": "Diagnostic Instruments",
    "code": "DIAG-INST",
    "item_category_id": 6,
    "description": "Handheld or portable devices for physical examination (e.g., stethoscopes, otoscopes)",
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
    "item_classificationss": [
        {
            "name": "Diagnostic Instruments",
            "code": "DIAG-INST",
            "item_category_id": 6,
            "description": "Handheld or portable devices for physical examination (e.g., stethoscopes, otoscopes)"
        },
        {
            "name": "Surgical Instruments",
            "code": "SURG-INST",
            "item_category_id": 2,
            "description": "Tools for performing surgical procedures (e.g., scalpels, forceps, retractors)"
        },
        {
            "name": "Imaging Equipment",
            "code": "IMG-EQ",
            "item_category_id": 1,
            "description": "Machines for medical imaging (e.g., X-ray, MRI, CT scanners)"
        }
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
        "name": "Diagnostic Instruments",
        "code": "DIAG-INST",
        "description": "Handheld or portable devices for physical examination (e.g., stethoscopes, otoscopes)",
        "item_category_id": "6",
        "updated_at": "2025-03-24T18:29:48.000000Z",
        "created_at": "2025-03-24T18:29:48.000000Z",
        "id": 1
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
            <pre>{
    "data": [
        {
            "id": 2,
            "item_category_id": 2,
            "name": "Surgical Instruments",
            "code": "SURG-INST",
            "description": "Tools for performing surgical procedures (e.g., scalpels, forceps, retractors)",
            "deleted_at": null,
            "created_at": "2025-03-24T18:39:54.000000Z",
            "updated_at": "2025-03-24T18:39:54.000000Z"
        },
        {
            "id": 3,
            "item_category_id": 1,
            "name": "Imaging Equipment",
            "code": "IMG-EQ",
            "description": "Machines for medical imaging (e.g., X-ray, MRI, CT scanners)",
            "deleted_at": null,
            "created_at": "2025-03-24T18:39:54.000000Z",
            "updated_at": "2025-03-24T18:39:54.000000Z"
        }
    ],
    "message": "Successfully created item_classificationss record",
    "metadata": {
        "methods": "[GET, POST, PUT ,DELETE]",
        "duplicate_items": [
            {
                "id": 1,
                "name": "Diagnostic Instruments",
                "code": "DIAG-INST",
                "description": "Handheld or portable devices for physical examination (e.g., stethoscopes, otoscopes)",
                "category": null,
                "created_at": "2025-03-24T18:38:46.000000Z",
                "updated_at": "2025-03-24T18:38:46.000000Z"
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
        <h2>PUT /api/item-classifications</h2>
        <p>Update one or more item classifications. Supports both single and bulk updates.</p>

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
                        The ID(s) of the classification(s) to update. Accepts multiple formats:
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
                    <td>Updated classification name</td>
                    <td>No (partial updates supported)</td>
                </tr>
                <tr>
                    <td>code</td>
                    <td>string</td>
                    <td>Updated classification code</td>
                    <td>No (partial updates supported)</td>
                </tr>
                <tr>
                    <td>description</td>
                    <td>string</td>
                    <td>Updated classification description</td>
                    <td>No</td>
                </tr>
                <tr>
                    <td>item_classifications</td>
                    <td>array</td>
                    <td>
                        Required for bulk updates. Array of objects containing:
                        <ul>
                            <li>name</li>
                            <li>code</li>
                            <li>description</li>
                        </ul>
                    </td>
                    <td>Conditional (required for bulk updates)</td>
                </tr>
            </tbody>
        </table>

        <h3>Example Requests</h3>
        
        <h4>Single Update</h4>
        <pre>
PUT {{ env('SERVER_DOMAIN') }}/api/item-classifications?id=1
Content-Type: application/json

{
    "name": "Medical Equipment",
    "code": "MED-EQP",
    "description": "Updated description"
}
        </pre>

        <h4>Bulk Update</h4>
        <pre>
PUT {{ env('SERVER_DOMAIN') }}/api/item-classifications?id[]=1&id[]=2
Content-Type: application/json

{
    "item_classifications": [
        {
            "name": "Updated Medical Equipment"
        },
        {
            "code": "NEW-CODE"
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
        "name": "Medical Equipment",
        "code": "MED-EQP",
        "description": "Updated description",
        "item_category_id": 1,
        "deleted_at": null,
        "created_at": "2025-03-24T06:02:44.000000Z",
        "updated_at": "2025-03-24T06:14:44.000000Z"
    },
    "message": "Item classification updated successfully.",
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

        <h4>Bulk Update Success</h4>
        <pre>
{
    "data": [
        {
            "id": 1,
            "name": "Updated Medical Equipment",
            "code": "MED-EQP",
            "description": null,
            "item_category_id": 1,
            "deleted_at": null,
            "created_at": "2025-03-24T06:02:44.000000Z",
            "updated_at": "2025-03-24T06:20:44.000000Z"
        },
        {
            "id": 2,
            "name": null,
            "code": "NEW-CODE",
            "description": null,
            "item_category_id": 1,
            "deleted_at": null,
            "created_at": "2025-03-24T06:02:44.000000Z",
            "updated_at": "2025-03-24T06:20:44.000000Z"
        }
    ],
    "message": "Successfully updated 2 item classifications.",
    "metadata": {
        "methods": "[GET, PUT, DELETE]",
        "formats": [
            "http://localhost:8000/api/item_classifications?id[]=1&id[]=2"
        ],
        "fields": [
            "code"
        ]
    }
}
        </pre>

        <h4>Partial Update (With Errors)</h4>
        <pre>
{
    "data": [
        {
            "id": 1,
            "name": "Updated Medical Equipment",
            "code": "MED-EQP",
            "description": null,
            "item_category_id": 1,
            "deleted_at": null,
            "created_at": "2025-03-24T06:02:44.000000Z",
            "updated_at": "2025-03-24T06:20:44.000000Z"
        }
    ],
    "message": "Partial update completed with errors.",
    "metadata": {
        "method": "[PUT]",
        "errors": [
            "Item classification with ID 2 not found."
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
                    <td>ID parameter is required</td>
                    <td>When no ID parameter is provided</td>
                </tr>
                <tr>
                    <td>422</td>
                    <td>Number of IDs does not match number of item classifications provided</td>
                    <td>When bulk update count mismatch occurs</td>
                </tr>
                <tr>
                    <td>422</td>
                    <td>Multiple IDs provided but no items array for bulk update</td>
                    <td>When multiple IDs are given without bulk data</td>
                </tr>
                <tr>
                    <td>404</td>
                    <td>Item not found</td>
                    <td>When single ID update fails</td>
                </tr>
                <tr>
                    <td>207</td>
                    <td>Partial update completed with errors</td>
                    <td>When bulk update has partial failures (Multi-Status)</td>
                </tr>
            </tbody>
        </table>

        <h3>Notes</h3>
        <ul>
            <li>All updates are partial - only provided fields will be updated</li>
            <li>In development mode, additional metadata is included in responses</li>
            <li>For bulk updates, the order of IDs must match the order of objects in item_classifications array</li>
            <li>Bulk updates will continue processing even if some items fail (returns 207 status)</li>
            <li>Empty updates (no valid fields provided) will be rejected</li>
        </ul>
    </div>

    <!-- Delete Endpoint -->
    <div class="endpoint">
        <h2>DELETE /api/item-classifications</h2>
        <p>Soft delete one or more item classifications (marks as deleted but retains in database).</p>

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
                        The ID(s) of the classification(s) to delete. Accepts multiple formats:
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
                        A query object to find classifications to delete (e.g., <code>{"code": "OFFICE"}</code>).
                        Will reject if matches multiple records.
                    </td>
                    <td>Conditional (required if no id)</td>
                </tr>
            </tbody>
        </table>

        <h3>Example Requests</h3>
        <h4>Delete by single ID</h4>
        <pre>
DELETE {{ env('SERVER_DOMAIN') }}/api/item-classifications?id=1
        </pre>

        <h4>Delete by multiple IDs</h4>
        <pre>
DELETE {{ env('SERVER_DOMAIN') }}/api/item-classifications?id=1,2,3
        </pre>

        <h4>Delete by query</h4>
        <pre>
DELETE {{ env('SERVER_DOMAIN') }}/api/item-classifications?query={"name":"Office Equipment"}
        </pre>

        <h3>Success Responses</h3>
        <h4>ID-based deletion</h4>
        <pre>
    {
        "message": "Successfully deleted 2 classification(s).",
        "deleted_ids": [1, 2]
    }
        </pre>

        <h4>Query-based deletion</h4>
        <pre>
    {
        "message": "Successfully deleted classification.",
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
                    <td>Invalid ID format provided.</td>
                    <td>When provided IDs are not valid numbers</td>
                </tr>
                <tr>
                    <td>404</td>
                    <td>No active classifications found...</td>
                    <td>When no matching active records found</td>
                </tr>
                <tr>
                    <td>409</td>
                    <td>Query matches multiple records...</td>
                    <td>When query matches multiple records (includes data in response)</td>
                </tr>
                <tr>
                    <td>422</td>
                    <td>Invalid request.</td>
                    <td>When neither parameter is provided (includes metadata in dev)</td>
                </tr>
            </tbody>
        </table>

        <h3>Notes</h3>
        <ul>
            <li>This is a soft delete operation - records are marked as deleted but remain in database</li>
            <li>Only active (non-deleted) records can be deleted</li>
            <li>In development mode, additional metadata is returned for invalid requests</li>
            <li>For query operations, the system will reject requests that would affect multiple records</li>
            <li>The endpoint returns the IDs of successfully deleted records</li>
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