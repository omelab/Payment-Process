<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Square Payment Form</title>
    <script type="text/javascript" src="https://sandbox.web.squarecdn.com/v1/square.js"></script> <!-- Use sandbox for testing -->
    {{-- <script type="text/javascript" src="https://web.squarecdn.com/v1/square.js"></script> --}} <!-- Use only for production -->
    <style>
        #payment-form-container {
            max-width: 400px;
            margin: auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        #card-container {
            margin-bottom: 20px;
        }

        #card-button {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px;
            width: 100%;
            font-size: 18px;
            cursor: pointer;
        }

        #card-button:hover {
            background-color: #0056b3;
        }
    </style>
</head>

<body>
    <h1>Square Payment</h1>

    <div id="payment-form-container">
        <div id="card-container"></div> <!-- Square will render the card payment fields here -->
        <button id="card-button" type="button">Pay</button>
    </div>

    <script>
        const squareApplicationId = "{{ env('SQUARE_APPLICATION_ID') }}";
        const squareEnvironment = "{{ env('SQUARE_ENVIRONMENT', 'sandbox') }}";

        async function initializeSquare() {
            if (!window.Square) {
                alert("Square payments SDK failed to load!");
                return;
            }

            const payments = window.Square.payments(squareApplicationId, squareEnvironment); // or 'production' for live
            const card = await payments.card();
            await card.attach('#card-container');

            // Add a listener to your button for payment submission
            document.getElementById('card-button').addEventListener('click', async function() {
                try {
                    const tokenResult = await card.tokenize();
                    if (tokenResult.status === 'OK') {
                        processPayment(tokenResult.token); // Proceed to process the payment
                    } else {
                        console.error("Tokenization failed.");
                    }
                } catch (e) {
                    console.error("Error occurred while tokenizing card details:", e);
                }
            });
        }

        // Function to send payment nonce to your Laravel backend
        async function processPayment(token) {
            const response = await fetch('{{ route('square_process') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}', // For Laravel CSRF protection
                },
                body: JSON.stringify({
                    nonce: token,
                    amount: 1000, // Example: 1000 cents = $10.00
                }),
            });

            const result = await response.json();

            if (result.success) {
                alert('Payment successful!');
            } else {
                alert('Payment failed: ' + result.message);
            }
        }

        initializeSquare(); // Initialize the Square payment form
    </script>
</body>

</html>
