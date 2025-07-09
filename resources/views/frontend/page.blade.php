<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buy Direct Explanation</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 font-sans">
    <div class="max-w-2xl mx-auto p-6">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">What is "Buy Direct"?</h2>
            
            <p class="text-gray-600 mb-4">
                Buy Direct is a fast and secure way to purchase items instantly on our platform—no need to wait for negotiations or back-and-forth messaging.
            </p>
            
            <button id="learnMoreBtn" class="text-blue-600 font-medium hover:text-blue-800 focus:outline-none mb-4">
                Learn more ↓
            </button>
            
            <div id="buyDirectContent" class="hidden transition-all duration-300">
                <p class="text-gray-600 mb-4">
                    When you see the Buy Direct button, it means the seller has enabled instant purchase. Simply click, pay, and the item is yours. It's the easiest way to shop with confidence.
                </p>
                
                <h3 class="text-xl font-semibold text-gray-800 mt-6 mb-3">How it works:</h3>
                
                <ul class="space-y-4">
                    <li class="flex items-start">
                        <div class="flex-shrink-0 h-6 w-6 text-blue-500 mr-3">✓</div>
                        <div>
                            <h4 class="font-medium text-gray-800">Instant Purchase</h4>
                            <p class="text-gray-600">Tap Buy Direct to immediately lock in the item before someone else does.</p>
                        </div>
                    </li>
                    
                    <li class="flex items-start">
                        <div class="flex-shrink-0 h-6 w-6 text-blue-500 mr-3">✓</div>
                        <div>
                            <h4 class="font-medium text-gray-800">Secure Payments with Paystack</h4>
                            <p class="text-gray-600">All payments are processed safely through Paystack, supporting cards, bank transfers, and other local methods.</p>
                        </div>
                    </li>
                    
                    <li class="flex items-start">
                        <div class="flex-shrink-0 h-6 w-6 text-blue-500 mr-3">✓</div>
                        <div>
                            <h4 class="font-medium text-gray-800">Trusted Delivery Partners</h4>
                            <p class="text-gray-600">Choose delivery through GIG Logistics, FedEx, or GUO Logistics—or arrange local pickup if offered by the seller.</p>
                        </div>
                    </li>
                    
                    <li class="flex items-start">
                        <div class="flex-shrink-0 h-6 w-6 text-blue-500 mr-3">✓</div>
                        <div>
                            <h4 class="font-medium text-gray-800">Buyer Protection</h4>
                            <p class="text-gray-600">Your payment is held securely until you confirm you've received the item in good condition. If you don't take any action after the item is successfully delivered, we'll automatically release the payout to the seller 7 days after delivery—giving you enough time to inspect the item and report any issues.</p>
                        </div>
                    </li>
                    
                    <li class="flex items-start">
                        <div class="flex-shrink-0 h-6 w-6 text-blue-500 mr-3">✓</div>
                        <div>
                            <h4 class="font-medium text-gray-800">Dispute Resolution</h4>
                            <p class="text-gray-600">If there's an issue with your order, you can open a dispute within 3 days of delivery. Our support team will step in to mediate and resolve the issue fairly—whether it's a refund, replacement, or other solution.</p>
                        </div>
                    </li>
                </ul>
                
                <div class="mt-6 p-4 bg-blue-50 rounded-lg">
                    <p class="text-blue-800 font-medium">
                        With Buy Direct, shopping is simpler, faster, and more reliable—giving you peace of mind every step of the way.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('learnMoreBtn').addEventListener('click', function() {
            const content = document.getElementById('buyDirectContent');
            const btn = document.getElementById('learnMoreBtn');
            
            if (content.classList.contains('hidden')) {
                content.classList.remove('hidden');
                btn.textContent = 'Show less ↑';
            } else {
                content.classList.add('hidden');
                btn.textContent = 'Learn more ↓';
            }
        });
    </script>
</body>
</html>