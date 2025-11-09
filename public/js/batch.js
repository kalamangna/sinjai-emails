document.addEventListener('DOMContentLoaded', function () {
    const nameInput = document.getElementById('name_input');
    const unitKerjaInput = document.getElementById('unit_kerja_input');
    const generateBtn = document.getElementById('generate_btn');
    const resultsTableBody = document.querySelector('#results_table tbody');
    const submitBtn = document.getElementById('submit_btn');
    
    let validUserBatch = [];

    generateBtn.addEventListener('click', async function () {
        const names = nameInput.value.trim().split('\n').filter(name => name.trim() !== '');
        const unitKerja = unitKerjaInput.value;

        if (names.length === 0) {
            alert('Please enter at least one name.');
            return;
        }
        if (!unitKerja) {
            alert('Please select a Unit Kerja.');
            return;
        }

        // Reset and disable buttons
        validUserBatch = [];
        generateBtn.disabled = true;
        generateBtn.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Generating...`;
        submitBtn.disabled = true;

        resultsTableBody.innerHTML = '<tr><td colspan="6" class="text-center">Generating and checking emails...</td></tr>';

        const generatedEmails = new Set();
        const userBatch = [];
        let allEmailsValid = true;

        for (const name of names) {
            const email = generateEmail(name);
            const password = generatePassword(name);
            const isDuplicate = generatedEmails.has(email);
            
            if (isDuplicate) {
                allEmailsValid = false;
            }
            
            generatedEmails.add(email);

            userBatch.push({
                name: name.trim(),
                unitKerja: unitKerja,
                email: email,
                password: password,
                quota: 1024, // Default quota
                isDuplicate: isDuplicate,
                isAvailable: false
            });
        }

        const checkPromises = userBatch.map(user => {
            if (user.isDuplicate) {
                return Promise.resolve(user);
            }
            return checkEmailAvailability(user.email).then(result => {
                user.isAvailable = result.available;
                if (!result.available) {
                    allEmailsValid = false;
                }
                return user;
            });
        });

        await Promise.all(checkPromises);

        renderResults(userBatch);

        generateBtn.disabled = false;
        generateBtn.innerHTML = `<i class="fas fa-cogs me-2"></i>Generate`;

        if (allEmailsValid) {
            submitBtn.disabled = false;
            // Store the valid users for submission
            validUserBatch = userBatch.filter(user => !user.isDuplicate && user.isAvailable);
        }
    });

    submitBtn.addEventListener('click', async function() {
        if (validUserBatch.length === 0) {
            alert('No valid emails to submit.');
            return;
        }

        submitBtn.disabled = true;
        submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Submitting...`;

        try {
            const response = await fetch('/email/batch_create', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(validUserBatch)
            });

            if (!response.ok) {
                throw new Error('Server responded with an error.');
            }

            const result = await response.json();
            handleSubmitResponse(result);

        } catch (error) {
            console.error('Error submitting batch:', error);
            alert('An unexpected error occurred during submission.');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = `<i class="fas fa-check-circle me-2"></i>Submit Batch`;
        }
    });

    function handleSubmitResponse(result) {
        if (result.success === false && result.message) {
            alert(`Submission failed: ${result.message}`);
            return;
        }

        let successCount = 0;
        result.results.forEach(res => {
            const row = Array.from(resultsTableBody.children).find(r => r.cells[3].textContent === res.email);
            if (row) {
                let statusCell = row.cells[5];
                if (res.success) {
                    statusCell.innerHTML = '<span class="badge bg-success">Created</span>';
                    successCount++;
                } else {
                    statusCell.innerHTML = `<span class="badge bg-danger" title="${res.message}">Failed</span>`;
                }
            }
        });

        if (successCount === result.results.length) {
            alert(`Successfully created all ${successCount} email accounts!`);
        }
        else {
            alert(`Batch submission completed with ${result.results.length - successCount} errors. Please review the status in the table.`);
        }
    }

    function generateEmail(name) {
        const domain = '@sinjaikab.go.id';
        const maxUsernameLength = 30 - domain.length;
        const username = name.toLowerCase().replace(/\s+/g, '').substring(0, maxUsernameLength);
        return `${username}${domain}`;
    }

    function generatePassword(name) {
        const day = new Date().getDate();
        const namePart = name.replace(/\s+/g, '').substring(0, 5).toLowerCase();
        if (!namePart) return `@${day}#`;
        const capitalizedNamePart = namePart.charAt(0).toUpperCase() + namePart.slice(1);
        return `${capitalizedNamePart}@${day}#`;
    }

    async function checkEmailAvailability(email) {
        try {
            const response = await fetch('/user/check_email', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ email: email })
            });
            if (!response.ok) {
                return { available: false, message: 'Server error during check.' };
            }
            return await response.json();
        } catch (error) {
            console.error('Error checking email availability:', error);
            return { available: false, message: 'Network error.' };
        }
    }

    function renderResults(userBatch) {
        resultsTableBody.innerHTML = '';
        if (userBatch.length === 0) {
            resultsTableBody.innerHTML = '<tr><td colspan="6" class="text-center">No names entered.</td></tr>';
            return;
        }

        userBatch.forEach((user, index) => {
            let statusBadge;
            if (user.isDuplicate) {
                statusBadge = '<span class="badge bg-warning text-dark">Duplicate</span>';
            } else if (user.isAvailable) {
                statusBadge = '<span class="badge bg-success">Available</span>';
            } else {
                statusBadge = '<span class="badge bg-danger">Used</span>';
            }

            const row = `
                <tr>
                    <td>${index + 1}</td>
                    <td>${user.name.toUpperCase()}</td>
                    <td>${user.unitKerja}</td>
                    <td>${user.email}</td>
                    <td>${user.password}</td>
                    <td class="text-center">${statusBadge}</td>
                </tr>
            `;
            resultsTableBody.insertAdjacentHTML('beforeend', row);
        });
    }
});