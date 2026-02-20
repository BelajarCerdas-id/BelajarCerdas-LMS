<main>
    <section class="bg-white shadow-lg rounded-lg p-8 border border-gray-200">
        <div id="editor-container" data-source="{{ $source }}"  data-question-type="{{ $questionType }}" data-sub-bab-id="{{ $subBabId }}" data-question-id="{{ $questionId }}" 
            data-school-name="{{ $schoolName }}" data-school-id="{{ $schoolId }}"
            data-upload-url="{{ route('lms.editImage', ['_token' => csrf_token()]) }}"
            data-delete-url="{{ route('lms.deleteImage') }}">
            <!---- form in ajax ---->
        </div>
    </section>
</main>