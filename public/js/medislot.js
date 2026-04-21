document.addEventListener('DOMContentLoaded', function() {
    const btnTambahJadwal = document.getElementById('btnTambahJadwal');
    const modalTambahJadwal = document.getElementById('modalTambahJadwal');
    const modalSuccess = document.getElementById('modalSuccess');
    const closeModal = document.querySelector('.close-modal');
    const btnContinue = document.getElementById('btnContinue');
    const formTambahJadwal = document.getElementById('formTambahJadwal');
    const inputDate = document.getElementById('inputDate');
    const inputTime = document.getElementById('inputTime');
    const availabilityStatus = document.getElementById('availabilityStatus');

    // Open Modal
    btnTambahJadwal.addEventListener('click', () => {
        modalTambahJadwal.style.display = 'block';
    });

    // Close Modal
    closeModal.addEventListener('click', () => {
        modalTambahJadwal.style.display = 'none';
    });

    window.onclick = (event) => {
        if (event.target == modalTambahJadwal) {
            modalTambahJadwal.style.display = 'none';
        }
    };

    // Check Availability on Change
    const checkSlot = async () => {
        if (inputDate.value && inputTime.value) {
            availabilityStatus.textContent = 'Memeriksa ketersediaan...';
            availabilityStatus.style.color = '#7f8c8d';
            
            try {
                const response = await fetch(`/api/check-availability?date=${inputDate.value}&time=${inputTime.value}`);
                const data = await response.json();
                
                if (data.available) {
                    availabilityStatus.textContent = 'Slot tersedia!';
                    availabilityStatus.style.color = '#27ae60';
                } else {
                    availabilityStatus.textContent = 'Slot tidak tersedia.';
                    availabilityStatus.style.color = '#e74c3c';
                }
            } catch (error) {
                console.error('Error checking availability:', error);
            }
        }
    };

    inputDate.addEventListener('change', checkSlot);
    inputTime.addEventListener('change', checkSlot);

    // Form Submission
    formTambahJadwal.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(formTambahJadwal);
        const data = Object.fromEntries(formData.entries());

        try {
            const response = await fetch('/schedules', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (response.ok) {
                modalTambahJadwal.style.display = 'none';
                modalSuccess.style.display = 'block';
                formTambahJadwal.reset();
            } else {
                alert(result.message || 'Terjadi kesalahan saat menyimpan jadwal.');
            }
        } catch (error) {
            console.error('Error saving schedule:', error);
            alert('Gagal terhubung ke server.');
        }
    });

    // Continue Button
    btnContinue.addEventListener('click', () => {
        modalSuccess.style.display = 'none';
        window.location.reload(); // Simple way to refresh the list
    });
});
