@include('frontend.layouts.header')
@include('frontend.layouts.nav')
@include('frontend.components.mobile.mobile-nav')
@include('frontend.layouts.search')


<section class="bg-gray-50 py-10 mb-10 px-4 sm:px-6 lg:px-8">
  <div class="max-w-4xl mx-auto">
    <h2 class="text-2xl font-bold text-gray-900 text-center mb-10">Frequently Asked Questions</h2>

    <div class="space-y-6">
      <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-lg font-semibold text-gray-800">How do I post an item for sale?</h3>
        <p class="text-gray-600 mt-2">
          Simply create an account or log in, then click on the "Post Ad" button. Fill in the details of your item, upload clear photos, and submit — your item will be live shortly.
        </p>
      </div>

      <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-lg font-semibold text-gray-800">Is it free to use Marketplace NG?</h3>
        <p class="text-gray-600 mt-2">
          Yes, posting and browsing ads on Marketplace NG is completely free. We may offer premium features for increased visibility.
        </p>
      </div>

      <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-lg font-semibold text-gray-800">How can I contact a seller?</h3>
        <p class="text-gray-600 mt-2">
          Each ad has a contact section where you can call or message the seller directly. Always ensure you meet in a safe, public place when transacting.
        </p>
      </div>

      <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-lg font-semibold text-gray-800">How do I report a suspicious ad?</h3>
        <p class="text-gray-600 mt-2">
          If you come across any suspicious listing or user, please click the "Report" button on the ad page or contact our support team for immediate assistance.
        </p>
      </div>

      <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-lg font-semibold text-gray-800">Can businesses use Marketplace NG?</h3>
        <p class="text-gray-600 mt-2">
          Absolutely! Marketplace NG is open to both individuals and businesses. You can use the platform to reach a wider audience and grow your customer base.
        </p>
      </div>
    </div>
  </div>
</section>



@include('frontend.layouts.footer')


