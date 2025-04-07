@extends('layout')

@section('component')
    <div class="endpoint">
        <form onsubmit="fetchResponse(event)">
            <h2>GET /api/ppmp-applications</h2>
            <p>Retrieve all active PPMP Applications.</p>
        
            <h3>Example Request</h3>
            <pre>GET {{ env('SERVER_DOMAIN') }}/api/ppmp-applications
                <button class="copy-button" type="button" onclick="copyToClipboard('{{ env('SERVER_DOMAIN') }}/api/ppmp-applications')">Copy</button>
            </pre>

            {{-- <label for="rawJson">Parameters:</label>
            <div class="row mb-3">
                <div class="col-auto">
                  <label class="col-form-label">page (integer)</label>
                </div>
                <div class="col-auto">
                  <input type="number" class="form-control">
                </div>
              </div> --}}

            <div class="d-grid gap-2">
                <button class="btn btn-success">Execute</button>
            </div>

              
            {{-- <textarea id="rawJson" rows="10"></textarea> --}}
        </form>

        <h2>Response:</h2>
        <pre id="response">Loading...</pre>
    </div>

    <script>
        async function fetchResponse(event) {
            event.preventDefault();
            
            const url = "{{ env('SERVER_DOMAIN') }}/api/ppmp-applications"; // Ensure correct endpoint
            const responseElement = document.getElementById('response');
            
            responseElement.textContent = "Loading...";

            try {
                const response = await fetch(url, { method: 'GET' });

                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }

                const result = await response.json();
                responseElement.textContent = JSON.stringify(result, null, 2);
            } catch (error) {
                responseElement.textContent = `Error: ${error.message}`;
            }
        }
    </script>
@endsection
