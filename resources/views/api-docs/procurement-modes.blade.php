<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Documentation - Procurement Modes</title>
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
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="{{ url('/api-docs') }}" class="back-link">← Back to API Documentation</a>
        <h1>API Documentation - Procurement Modes</h1>
                 
        <div class="resource-description">
            <h2>Procurement Modes Resource</h2>
            <p>
                The Procurement Modes resource defines the standardized methods for acquiring inventory,
                ensuring alignment with organizational policies and regulatory requirements. These modes
                provide transparency and accountability throughout the procurement lifecycle.
            </p>
            
            <h3>Key Features</h3>
            <ul>
                <li><strong>Documents the purpose behind procurement decisions</strong> - Records the rationale for selecting specific acquisition methods</li>
                <li><strong>Aligns procurement with organizational strategy</strong> - Ensures purchasing methods support broader business objectives</li>
                <li><strong>Supports justification for budget allocations</strong> - Provides audit trails for financial planning and expenditure approvals</li>
                <li><strong>Enables tracking of procurement goal achievement</strong> - Measures performance against established purchasing targets</li>
                <li><strong>Maintains historical records through soft deletion</strong> - Preserves procurement history while keeping active records clean</li>
                <li><strong>Standardizes purchasing processes</strong> - Creates consistent methods across all departments and locations</li>
                <li><strong>Facilitates compliance monitoring</strong> - Helps ensure adherence to regulatory and internal policy requirements</li>
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
                        <td>name</td>
                        <td>string</td>
                        <td>Unique mode/type identifier</td>
                        <td>Yes</td>
                        <td>"PAT_SAFETY"</td>
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

        <div class="usage-notes">
            <h2>Usage Notes</h2>
            <ul>
                <li>Procurement mode names should be unique across the system</li>
                <li>Once created, procurement modes should not be deleted but rather marked inactive</li>
                <li>Existing modes should only be modified if they haven't been used in transactions</li>
                <li>Consider localization requirements for mode names if applicable</li>
            </ul>
        </div>

        <!-- Index Endpoint -->
        <div class="endpoint">
            <h2>GET /api/procurement-modes</h2>
            <p>Retrieve all active procurement modes.</p>

            <h3>Example Request</h3>
            <pre>
GET {{ env('SERVER_DOMAIN') }}/api/procurement-modes
                <button class="copy-button" onclick="copyToClipboard('{{ env('SERVER_DOMAIN') }}/api/procurement-modes')">
                    <i class="fas fa-copy"></i> Copy URL
                </button>
            </pre>

            <h3>Example Response</h3>
            <pre>
{
    "data": [
        {
            "id": 1,
            "name": "Public Bidding",
            "deleted_at": null
        },
        {
            "id": 2,
            "name": "Direct Purchase",
            "deleted_at": null
        }
    ],
    "metadata": {
        "methods": ["GET, POST, PUT, DELETE"]
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
                        <td>500</td>
                        <td>Internal server error</td>
                        <td>Returned when an unexpected error occurs</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Store Endpoint -->
        <div class="endpoint">
            <h2>POST /api/procurement-modes</h2>
            <p>Create a new procurement mode.</p>

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
                        <td>Name of the procurement mode</td>
                        <td>Yes</td>
                    </tr>
                </tbody>
            </table>

            <h3>Example Request</h3>
            <pre>
POST {{ env('SERVER_DOMAIN') }}/api/procurement-modes
Content-Type: application/json

{
    "name": "Emergency Purchase"
}
                <button class="copy-button" onclick="copyToClipboard('{{ env('SERVER_DOMAIN') }}/api/procurement-modes')">
                    <i class="fas fa-copy"></i> Copy URL
                </button>
            </pre>

            <h3>Example Response (Success)</h3>
            <pre>
{
    "data": {
        "id": 3,
        "name": "Emergency Purchase",
        "deleted_at": null
    },
    "metadata": {
        "methods": ["GET, POST, PUT, DELETE"]
    }
}
            </pre>

            <h3>Example Response (Error - Duplicate)</h3>
            <pre>
{
    "message": "Procurement mode already exist."
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
                        <td>Procurement mode already exist</td>
                        <td>Returned when a mode with the same name exists</td>
                    </tr>
                    <tr>
                        <td>422</td>
                        <td>Field name is required</td>
                        <td>Returned when the name field is missing</td>
                    </tr>
                    <tr>
                        <td>500</td>
                        <td>Internal server error</td>
                        <td>Returned when an unexpected error occurs</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Update Endpoint -->
        <div class="endpoint">
            <h2>PUT /api/procurement-modes/{procurementMode}</h2>
            <p>Update an existing procurement mode.</p>

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
                        <td>procurementMode</td>
                        <td>integer</td>
                        <td>ID of the procurement mode to update</td>
                        <td>Yes</td>
                    </tr>
                </tbody>
            </table>

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
                        <td>Updated name of the procurement mode</td>
                        <td>Yes</td>
                    </tr>
                </tbody>
            </table>

            <h3>Example Request</h3>
            <pre>
PUT {{ env('SERVER_DOMAIN') }}/api/procurement-modes/3
Content-Type: application/json

{
    "name": "Emergency Procurement"
}
                <button class="copy-button" onclick="copyToClipboard('{{ env('SERVER_DOMAIN') }}/api/procurement-modes/3')">
                    <i class="fas fa-copy"></i> Copy URL
                </button>
            </pre>

            <h3>Example Response</h3>
            <pre>
{
    "data": {
        "id": 3,
        "name": "Emergency Procurement",
        "deleted_at": null
    },
    "metadata": {
        "methods": ["GET, POST, PUT, DELETE"]
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
                        <td>Not found</td>
                        <td>Returned when the procurement mode doesn't exist</td>
                    </tr>
                    <tr>
                        <td>422</td>
                        <td>Validation error</td>
                        <td>Returned when the request body is invalid</td>
                    </tr>
                    <tr>
                        <td>500</td>
                        <td>Internal server error</td>
                        <td>Returned when an unexpected error occurs</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Delete Endpoint -->
        <div class="endpoint">
            <h2>DELETE /api/procurement-modes/{procurementMode}</h2>
            <p>Soft delete a procurement mode (marks as deleted but retains in database).</p>

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
                        <td>procurementMode</td>
                        <td>integer</td>
                        <td>ID of the procurement mode to delete</td>
                        <td>Yes</td>
                    </tr>
                </tbody>
            </table>

            <h3>Example Request</h3>
            <pre>
DELETE {{ env('SERVER_DOMAIN') }}/api/procurement-modes/3
                <button class="copy-button" onclick="copyToClipboard('{{ env('SERVER_DOMAIN') }}/api/procurement-modes/3')">
                    <i class="fas fa-copy"></i> Copy URL
                </button>
            </pre>

            <h3>Example Response</h3>
            <pre>
{
    "message": "Procurement mode deleted successfully"
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
                        <td>Not found</td>
                        <td>Returned when the procurement mode doesn't exist</td>
                    </tr>
                    <tr>
                        <td>500</td>
                        <td>Internal server error</td>
                        <td>Returned when an unexpected error occurs</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Common Procurement Modes -->
        <div class="usage-notes">
            <h2>Common Procurement Modes</h2>
            <p>Here are some commonly used procurement modes:</p>
            <ul>
                <li><strong>Public Bidding</strong> - Competitive bidding process open to all qualified suppliers</li>
                <li><strong>Direct Purchase</strong> - Direct procurement from a single supplier</li>
                <li><strong>Emergency Purchase</strong> - Expedited procurement for urgent needs</li>
                <li><strong>Framework Agreement</strong> - Long-term agreement with pre-selected suppliers</li>
                <li><strong>Negotiated Procurement</strong> - Procurement through direct negotiations</li>
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