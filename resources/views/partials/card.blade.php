<a href="#" class="grid grid-cols-2 p-4 items-center bg-white border border-gray-200 rounded-lg shadow-sm hover:bg-gray-100">

      <div class="flex items-center justify-center h-full bg-green-600 rounded-lg p-3 ">
          {!! $icon !!}
      </div>

      <div class="flex flex-col justify-between p-4 h-full leading-normal">
        <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900">
            {{ $value }}
        </h5>
        <p class="mb-3 font-normal text-gray-700">
            {{ $label }}
        </p>
      </div>
</a>
