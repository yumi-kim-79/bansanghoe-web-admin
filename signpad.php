<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signature Pad Example</title>
    <style>
        canvas {
            border: 1px solid #ccc;
            display: block;
            margin: 20px auto;
        }
        .buttons {
            text-align: center;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <h1 style="text-align: center;">Signature Pad</h1>
    <canvas id="signatureCanvas" width="600" height="300"></canvas>
    <div class="buttons">
        <button id="clearButton">Clear</button>
        <button id="saveButton">Save</button>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
    <script>
        // Get canvas and buttons
        const canvas = document.getElementById('signatureCanvas');
        const clearButton = document.getElementById('clearButton');
        const saveButton = document.getElementById('saveButton');

        // Initialize SignaturePad
        const signaturePad = new SignaturePad(canvas);

        // Clear button functionality
        clearButton.addEventListener('click', () => {
            signaturePad.clear();
        });

        // Save button functionality
        saveButton.addEventListener('click', () => {
            if (signaturePad.isEmpty()) {
                alert('Please provide a signature first.');
            } else {
                const dataURL = signaturePad.toDataURL(); // Default is PNG
                console.log(dataURL);

                // Optionally, you can create an image element to display the saved signature
                const img = document.createElement('img');
                img.src = dataURL;
                document.body.appendChild(img);
            }
        });
    </script>
</body>
</html>