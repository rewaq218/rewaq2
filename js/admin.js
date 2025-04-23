// ملف JavaScript للمشرف

// عند تحميل الصفحة
document.addEventListener('DOMContentLoaded', function() {
    // التحقق من تسجيل الدخول
    checkLogin();
    
    // إضافة معالج حدث لنموذج تسجيل الدخول
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', handleLogin);
    }
});

// التحقق من تسجيل الدخول
function checkLogin() {
    const isLoggedIn = localStorage.getItem('isLoggedIn');
    if (isLoggedIn === 'true') {
        showAdminDashboard();
        loadStudents();
    } else {
        showLoginPage();
    }
}

// إظهار صفحة تسجيل الدخول
function showLoginPage() {
    document.getElementById('loginPage').style.display = 'flex';
    document.getElementById('adminDashboard').style.display = 'none';
}

// إظهار لوحة تحكم المشرف
function showAdminDashboard() {
    document.getElementById('loginPage').style.display = 'none';
    document.getElementById('adminDashboard').style.display = 'block';
}

// معالجة تسجيل الدخول
async function handleLogin(e) {
    e.preventDefault();
    
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;
    
    try {
        const formData = new FormData();
        formData.append('username', username);
        formData.append('password', password);
        
        const response = await fetch('php/login.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            localStorage.setItem('isLoggedIn', 'true');
            showAdminDashboard();
            loadStudents();
        } else {
            document.getElementById('loginError').style.display = 'block';
            document.getElementById('loginError').textContent = data.message || 'اسم المستخدم أو كلمة المرور غير صحيحة';
        }
    } catch (error) {
        console.error('Error during login:', error);
        document.getElementById('loginError').style.display = 'block';
        document.getElementById('loginError').textContent = 'حدث خطأ أثناء تسجيل الدخول. الرجاء المحاولة مرة أخرى.';
    }
}

// تسجيل الخروج
function logout() {
    localStorage.removeItem('isLoggedIn');
    showLoginPage();
    document.getElementById('loginError').style.display = 'none';
    document.getElementById('username').value = '';
    document.getElementById('password').value = '';
}

// تحميل بيانات الطلاب
async function loadStudents() {
    document.getElementById('loadingIndicator').style.display = 'block';
    document.getElementById('studentsTable').style.display = 'none';
    document.getElementById('errorMessage').style.display = 'none';
    
    try {
        // محاولة الاتصال بقاعدة البيانات مع إعادة المحاولة
        let retries = 3;
        let result;
        
        while (retries > 0) {
            try {
                const response = await fetch('php/get_students.php');
                result = await response.json();
                
                if (result.success) {
                    break;
                } else {
                    throw new Error(result.message || 'فشل في جلب البيانات');
                }
            } catch (retryError) {
                console.error(`Error loading students (retries left: ${retries-1}):`, retryError);
                retries--;
                if (retries === 0) {
                    throw retryError;
                }
                // انتظار قبل إعادة المحاولة
                await new Promise(resolve => setTimeout(resolve, 1000));
            }
        }
        
        if (result.success) {
            renderStudents(result.data);
            document.getElementById('loadingIndicator').style.display = 'none';
            document.getElementById('studentsTable').style.display = 'block';
        } else {
            throw new Error('فشل في جلب البيانات');
        }
    } catch (error) {
        document.getElementById('loadingIndicator').style.display = 'none';
        document.getElementById('errorMessage').style.display = 'block';
        document.getElementById('errorMessage').textContent = `حدث خطأ أثناء جلب البيانات: ${error.message}`;
        console.error('Error loading students:', error);
    }
}

// عرض بيانات الطلاب في الجدول
function renderStudents(students) {
    const tableBody = document.getElementById('studentsTableBody');
    tableBody.innerHTML = '';
    
    if (students.length === 0) {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td colspan="8" class="text-center">لا توجد بيانات متاحة</td>
        `;
        tableBody.appendChild(row);
        return;
    }
    
    students.forEach(student => {
        const row = document.createElement('tr');
        
        // تحويل المستويات إلى العربية
        let levelInArabic = student.level;
        if (student.level === 'preparatory') levelInArabic = 'تمهيدية';
        else if (student.level === 'intermediate') levelInArabic = 'متوسطة';
        else if (student.level === 'specialized') levelInArabic = 'تخصصية';
        
        // تحويل نظام الحضور إلى العربية
        let attendanceInArabic = student.attendance_system;
        if (student.attendance_system === 'inPerson') attendanceInArabic = 'مباشر';
        else if (student.attendance_system === 'remote') attendanceInArabic = 'عن بعد';
        
        // تنسيق التاريخ
        const createdAt = new Date(student.created_at).toLocaleDateString('ar-EG');
        
        row.innerHTML = `
            <td>${student.reference_number}</td>
            <td>${student.full_name}</td>
            <td>${student.governorate}</td>
            <td>${student.whatsapp_number}</td>
            <td>${levelInArabic}</td>
            <td>${attendanceInArabic}</td>
            <td>${createdAt}</td>
            <td>
                <button 
                    onclick="viewStudentDetails('${student.id}')" 
                    class="btn btn-sm btn-primary"
                >
                    عرض التفاصيل
                </button>
            </td>
        `;
        
        tableBody.appendChild(row);
    });
}

// عرض تفاصيل الطالب
async function viewStudentDetails(studentId) {
    try {
        const response = await fetch(`php/get_student.php?id=${studentId}`);
        const result = await response.json();
        
        if (result.success) {
            // إنشاء نافذة منبثقة لعرض التفاصيل
            const student = result.data;
            
            // تحويل المستويات إلى العربية
            let levelInArabic = student.level;
            if (student.level === 'preparatory') levelInArabic = 'تمهيدية';
            else if (student.level === 'intermediate') levelInArabic = 'متوسطة';
            else if (student.level === 'specialized') levelInArabic = 'تخصصية';
            
            // تحويل نظام الحضور إلى العربية
            let attendanceInArabic = student.attendance_system;
            if (student.attendance_system === 'inPerson') attendanceInArabic = 'مباشر';
            else if (student.attendance_system === 'remote') attendanceInArabic = 'عن بعد';
            
            // تحويل النوع إلى العربية
            let genderInArabic = student.gender;
            if (student.gender === 'male') genderInArabic = 'ذكر';
            else if (student.gender === 'female') genderInArabic = 'أنثى';
            
            // تحويل نوع التعليم إلى العربية
            let educationTypeInArabic = student.education_type;
            if (student.education_type === 'general') educationTypeInArabic = 'عام';
            else if (student.education_type === 'azhar') educationTypeInArabic = 'أزهري';
            else if (student.education_type === 'other') educationTypeInArabic = 'أخرى';
            
            // تحويل المذهب إلى العربية
            let schoolInArabic = student.school;
            if (student.school === 'maliki') schoolInArabic = 'مالكي';
            else if (student.school === 'hanafi') schoolInArabic = 'حنفي';
            else if (student.school === 'shafii') schoolInArabic = 'شافعي';
            
            // تحويل ذوي الهمم إلى العربية
            let specialNeedsInArabic = student.special_needs;
            if (student.special_needs === 'yes') specialNeedsInArabic = 'نعم';
            else if (student.special_needs === 'no') specialNeedsInArabic = 'لا';
            
            // إنشاء محتوى النافذة المنبثقة
            const modalContent = `
                <div class="student-details">
                    <h2>بيانات الطالب: ${student.full_name}</h2>
                    
                    <div class="details-section">
                        <h3>البيانات الشخصية</h3>
                        <div class="details-grid">
                            <div class="detail-item">
                                <span class="detail-label">الرقم المرجعي:</span>
                                <span class="detail-value">${student.reference_number}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">الاسم:</span>
                                <span class="detail-value">${student.full_name}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">رقم جواز السفر:</span>
                                <span class="detail-value">${student.passport_number}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">رقم الواتس:</span>
                                <span class="detail-value">${student.whatsapp_number}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">النوع:</span>
                                <span class="detail-value">${genderInArabic}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">السن:</span>
                                <span class="detail-value">${student.age}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">المحافظة:</span>
                                <span class="detail-value">${student.governorate}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">محل الإقامة:</span>
                                <span class="detail-value">${student.residence}</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="details-section">
                        <h3>البيانات التعليمية</h3>
                        <div class="details-grid">
                            <div class="detail-item">
                                <span class="detail-label">المؤهل الدراسي:</span>
                                <span class="detail-value">${student.qualification}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">نوع التعليم:</span>
                                <span class="detail-value">${educationTypeInArabic}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">الوظيفة:</span>
                                <span class="detail-value">${student.job || 'غير محدد'}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">السنة الدراسية:</span>
                                <span class="detail-value">${student.academic_year || 'غير محدد'}</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="details-section">
                        <h3>بيانات التسجيل</h3>
                        <div class="details-grid">
                            <div class="detail-item">
                                <span class="detail-label">المرحلة:</span>
                                <span class="detail-value">${levelInArabic}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">التخصص:</span>
                                <span class="detail-value">${student.specialization || 'غير محدد'}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">المذهب:</span>
                                <span class="detail-value">${schoolInArabic}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">نظام الحضور:</span>
                                <span class="detail-value">${attendanceInArabic}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">من ذوي الهمم:</span>
                                <span class="detail-value">${specialNeedsInArabic}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">تاريخ التسجيل:</span>
                                <span class="detail-value">${new Date(student.created_at).toLocaleDateString('ar-EG')}</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="details-section">
                        <h3>المستندات</h3>
                        <div class="documents-grid">
                            ${student.id_card_url ? `
                                <div class="document-item">
                                    <h4>صورة البطاقة</h4>
                                    <a href="${student.id_card_url}" target="_blank">
                                        <img src="${student.id_card_url}" alt="صورة البطاقة" class="document-image">
                                    </a>
                                </div>
                            ` : ''}
                            
                            ${student.qualification_url ? `
                                <div class="document-item">
                                    <h4>صورة المؤهل</h4>
                                    <a href="${student.qualification_url}" target="_blank">
                                        <img src="${student.qualification_url}" alt="صورة المؤهل" class="document-image">
                                    </a>
                                </div>
                            ` : ''}
                            
                            ${student.payment_receipt_url ? `
                                <div class="document-item">
                                    <h4>صورة إيصال الدفع</h4>
                                    <a href="${student.payment_receipt_url}" target="_blank">
                                        <img src="${student.payment_receipt_url}" alt="صورة إيصال الدفع" class="document-image">
                                    </a>
                                </div>
                            ` : ''}
                        </div>
                    </div>
                </div>
            `;
            
            // عرض النافذة المنبثقة
            showModal('تفاصيل الطالب', modalContent);
        } else {
            alert('حدث خطأ أثناء جلب بيانات الطالب: ' + result.message);
        }
    } catch (error) {
        console.error('Error fetching student details:', error);
        alert('حدث خطأ أثناء جلب بيانات الطالب');
    }
}

// عرض نافذة منبثقة
function showModal(title, content) {
    // إنشاء عناصر النافذة المنبثقة
    const modal = document.createElement('div');
    modal.className = 'modal';
    modal.innerHTML = `
        <div class="modal-content">
            <div class="modal-header">
                <h2>${title}</h2>
                <span class="close-modal">&times;</span>
            </div>
            <div class="modal-body">
                ${content}
            </div>
            <div class="modal-footer">
                <button class="btn" onclick="closeModal()">إغلاق</button>
            </div>
        </div>
    `;
    
    // إضافة النافذة المنبثقة إلى الصفحة
    document.body.appendChild(modal);
    
    // إضافة معالج حدث لزر الإغلاق
    const closeButton = modal.querySelector('.close-modal');
    closeButton.addEventListener('click', () => {
        closeModal();
    });
    
    // إظهار النافذة المنبثقة
    setTimeout(() => {
        modal.style.display = 'flex';
    }, 100);
}

// إغلاق النافذة المنبثقة
function closeModal() {
    const modal = document.querySelector('.modal');
    if (modal) {
        modal.style.opacity = '0';
        setTimeout(() => {
            modal.remove();
        }, 300);
    }
}

// تصدير البيانات إلى Excel
async function exportToExcel() {
    try {
        document.getElementById('exportButton').disabled = true;
        document.getElementById('exportButton').textContent = 'جاري التصدير...';
        
        const response = await fetch('php/export_excel.php');
        
        if (response.ok) {
            // تحميل الملف
            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'بيانات_الطلاب.xlsx';
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            a.remove();
            
            alert('تم تصدير البيانات بنجاح');
        } else {
            const errorData = await response.json();
            throw new Error(errorData.message || 'فشل في تصدير البيانات');
        }
    } catch (error) {
        console.error('Error exporting data:', error);
        alert('حدث خطأ أثناء تصدير البيانات: ' + error.message);
    } finally {
        document.getElementById('exportButton').disabled = false;
        document.getElementById('exportButton').textContent = 'تصدير إلى Excel';
    }
}
