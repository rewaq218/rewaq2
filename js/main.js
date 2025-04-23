// ملف JavaScript الرئيسي

// عند تحميل الصفحة
document.addEventListener('DOMContentLoaded', function() {
    // التعامل مع نموذج التسجيل
    const form = document.getElementById('registrationForm');
    if (form) {
        form.addEventListener('submit', submitForm);
    }

    // تفعيل وظيفة معاينة الصور
    setupImagePreviews();
    
    // تفعيل وظيفة التخصص
    setupSpecializationToggle();
});

// وظيفة إرسال النموذج
async function submitForm(event) {
    event.preventDefault();
    
    // إظهار مؤشر التحميل
    const submitButton = document.getElementById('submitButton');
    submitButton.disabled = true;
    submitButton.textContent = 'جاري التسجيل...';
    
    try {
        // التحقق من الرقم المرجعي
        const referenceNumber = document.querySelector('input[name="referenceNumber"]').value;
        const checkResult = await checkReferenceNumber(referenceNumber);
        
        if (checkResult.exists) {
            showError('هذا الرقم المرجعي مستخدم بالفعل. الرجاء استخدام رقم مرجعي آخر.');
            submitButton.disabled = false;
            submitButton.textContent = 'تسجيل البيانات';
            return;
        }
        
        // جمع بيانات النموذج
        const formData = new FormData(form);
        
        // رفع الملفات وحفظ البيانات
        const result = await saveStudent(formData);
        
        if (result.success) {
            // عرض رسالة النجاح
            document.getElementById('referenceNumber').textContent = referenceNumber;
            document.getElementById('registrationForm').style.display = 'none';
            document.getElementById('successMessage').style.display = 'block';
        } else {
            showError('حدث خطأ أثناء حفظ البيانات: ' + result.message);
        }
    } catch (error) {
        console.error('Error submitting form:', error);
        showError('حدث خطأ غير متوقع. الرجاء المحاولة مرة أخرى.');
    } finally {
        // إعادة تفعيل زر التقديم
        submitButton.disabled = false;
        submitButton.textContent = 'تسجيل البيانات';
    }
}

// التحقق من وجود الرقم المرجعي
async function checkReferenceNumber(referenceNumber) {
    try {
        const response = await fetch('php/check_reference.php?reference=' + encodeURIComponent(referenceNumber));
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Error checking reference number:', error);
        return { error: true, message: error.message };
    }
}

// حفظ بيانات الطالب
async function saveStudent(formData) {
    try {
        const response = await fetch('php/save_student.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Error saving student data:', error);
        return { success: false, message: error.message };
    }
}

// إعداد معاينة الصور
function setupImagePreviews() {
    const imageInputs = document.querySelectorAll('input[type="file"]');
    
    imageInputs.forEach(input => {
        input.addEventListener('change', function() {
            const previewId = this.getAttribute('data-preview');
            if (previewId) {
                previewImage(this, previewId);
            }
        });
    });
}

// معاينة الصورة
function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    const previewImg = preview.querySelector('img');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            preview.classList.remove('hidden');
        }
        
        reader.readAsDataURL(input.files[0]);
    } else {
        previewImg.src = '';
        preview.classList.add('hidden');
    }
}

// إعداد تبديل التخصص
function setupSpecializationToggle() {
    const levelSelect = document.getElementById('levelSelect');
    if (levelSelect) {
        levelSelect.addEventListener('change', toggleSpecialization);
        // تنفيذ الوظيفة مرة واحدة عند تحميل الصفحة
        toggleSpecialization();
    }
}

// تبديل حقل التخصص
function toggleSpecialization() {
    const levelSelect = document.getElementById('levelSelect');
    const specializationDiv = document.getElementById('specializationDiv');
    const qualificationImage = document.getElementById('qualificationImage');
    
    if (!levelSelect || !specializationDiv) return;
    
    // التعامل مع قسم التخصص
    if (levelSelect.value === 'specialized') {
        specializationDiv.style.display = 'block';
        specializationDiv.querySelector('select').required = true;
    } else {
        specializationDiv.style.display = 'none';
        specializationDiv.querySelector('select').required = false;
    }
    
    // التعامل مع حقل صورة المؤهل
    if (qualificationImage) {
        if (levelSelect.value === 'intermediate' || levelSelect.value === 'specialized') {
            qualificationImage.required = true;
        } else {
            qualificationImage.required = false;
        }
    }
}

// عرض رسالة خطأ
function showError(message) {
    const errorDiv = document.getElementById('errorMessage');
    if (errorDiv) {
        errorDiv.textContent = message;
        errorDiv.style.display = 'block';
        
        // إخفاء رسالة الخطأ بعد 5 ثوان
        setTimeout(() => {
            errorDiv.style.display = 'none';
        }, 5000);
    } else {
        alert(message);
    }
}

// إعادة تعيين النموذج
function resetForm() {
    document.getElementById('registrationForm').reset();
    document.getElementById('registrationForm').style.display = 'block';
    document.getElementById('successMessage').style.display = 'none';
    
    // إعادة تعيين معاينات الصور
    const previews = document.querySelectorAll('.preview-container');
    previews.forEach(preview => {
        preview.classList.add('hidden');
    });
}
