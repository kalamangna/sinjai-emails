document.addEventListener("DOMContentLoaded", function () {
  const nameInput = document.getElementById("name_input");
  const nikNipInput = document.getElementById("nik_nip_input");
  
  // Mode selection elements
  const modeSingleRadio = document.getElementById("mode_single");
  const modeMultipleRadio = document.getElementById("mode_multiple");
  const singleUnitKerjaWrapper = document.getElementById("single_unit_kerja_wrapper");
  const multipleUnitKerjaWrapper = document.getElementById("multiple_unit_kerja_wrapper");
  const unitKerjaInputSingle = document.getElementById("unit_kerja_input_single");
  const unitKerjaInputMultiple = document.getElementById("unit_kerja_input_multiple");

  const generateBtn = document.getElementById("generate_btn");
  const resultsTableBody = document.querySelector("#results_table tbody");
  const submitBtn = document.getElementById("submit_btn");

  // Progress section elements
  const progressSection = document.getElementById("progress_section");
  const progressBar = document.getElementById("progress_bar");
  const progressText = document.getElementById("progress_text");
  const resultsLog = document.getElementById("results_log");

  let validUserBatch = [];
  let userBatch = [];

  // Create a Set of valid unit_kerja names for quick, case-insensitive lookup
  const validUnitKerjaNames = new Set(unitKerjaOptions.map(option => option.nama_unit_kerja.toLowerCase()));

  // Event listeners for mode switching
  modeSingleRadio.addEventListener('change', () => {
    singleUnitKerjaWrapper.style.display = 'block';
    multipleUnitKerjaWrapper.style.display = 'none';
  });

  modeMultipleRadio.addEventListener('change', () => {
    singleUnitKerjaWrapper.style.display = 'none';
    multipleUnitKerjaWrapper.style.display = 'block';
  });

  generateBtn.addEventListener("click", async function () {
    const names = nameInput.value.trim().split("\n").filter(name => name.trim() !== "");
    const nikNips = nikNipInput.value.trim().split("\n").filter(nikNip => nikNip.trim() !== "");
    const mode = document.querySelector('input[name="unitKerjaMode"]:checked').value;
    
    let unitKerjaValues = [];
    let validationError = "";

    if (names.length === 0) {
      validationError = "Please enter at least one name.";
    } else if (nikNips.length === 0) {
      validationError = "Please enter at least one NIK/NIP.";
    } else if (names.length !== nikNips.length) {
      validationError = "The number of names and NIK/NIPs must match.";
    }

    if (mode === 'single') {
      const singleUnitKerja = unitKerjaInputSingle.value;
      if (!singleUnitKerja) {
        validationError = "Please select a Unit Kerja.";
      } else {
        for (let i = 0; i < names.length; i++) {
          unitKerjaValues.push(singleUnitKerja);
        }
      }
    } else { // multiple mode
      unitKerjaValues = unitKerjaInputMultiple.value.trim().split("\n").filter(uk => uk.trim() !== "");
      if (unitKerjaValues.length > 0 && unitKerjaValues.length !== names.length) {
        validationError = "The number of Unit Kerja entries must match the number of names and NIK/NIPs.";
      }
      if (unitKerjaValues.length === 0) {
        for (let i = 0; i < names.length; i++) {
          unitKerjaValues.push("");
        }
      }
    }

    if (validationError) {
      alert(validationError);
      return;
    }

    generateBtn.disabled = true;
    generateBtn.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Generating...`;
    submitBtn.disabled = true;
    resultsTableBody.innerHTML = '<tr><td colspan="7" class="text-center">Generating and checking emails...</td></tr>';

    const generatedEmails = new Set();
    userBatch = [];
    for (let i = 0; i < names.length; i++) {
      const name = names[i];
      const cleanedName = name.replace(/[,.']/g, "");
      const nikNip = nikNips[i];
      const unitKerja = unitKerjaValues[i];
      const password = generatePassword(cleanedName);
      const { username: originalUsername, email: originalEmail } = generateEmail(cleanedName);
      
      let currentUsername = originalUsername;
      let currentEmail = originalEmail;
      let isAvailable = false;
      let attempts = 0;
      const maxAttempts = 10;

      while (attempts < maxAttempts) {
        attempts++;
        if (generatedEmails.has(currentEmail)) {
          const nikNipPart = getNikNipPart(nikNip);
          currentUsername = `${originalUsername}${nikNipPart}`;
          currentEmail = `${currentUsername}@sinjaikab.go.id`;
          continue;
        }

        const result = await checkEmailAvailability(currentEmail);
        if (result.available) {
          isAvailable = true;
          break;
        } else {
          const nikNipPart = getNikNipPart(nikNip);
          currentUsername = `${originalUsername}${nikNipPart}`;
          currentEmail = `${currentUsername}@sinjaikab.go.id`;
        }
      }

      const isDuplicate = generatedEmails.has(currentEmail);
      if (isDuplicate) isAvailable = false;

      userBatch.push({
        name: cleanedName.trim(),
        nikNip: nikNip.trim(),
        unitKerja: unitKerja.trim(),
        generatedUsername: currentUsername,
        email: currentEmail,
        password: password,
        quota: 1024,
        isDuplicate: isDuplicate,
        isAvailable: isAvailable,
        status: "pending",
      });
      generatedEmails.add(currentEmail);
    }

    renderResults(userBatch);
    generateBtn.disabled = false;
    generateBtn.innerHTML = `<i class="fas fa-cogs me-2"></i>Generate`;
    updateSubmitButtonState();
  });

  submitBtn.addEventListener("click", async function () {
    if (validUserBatch.length === 0) {
      alert("No valid emails to submit.");
      return;
    }

    submitBtn.disabled = true;
    submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Submitting...`;
    progressSection.style.display = "block";
    resultsLog.innerHTML = "";

    const totalToSubmit = validUserBatch.length;
    let successCount = 0;
    let failureCount = 0;

    try {
      for (let i = 0; i < totalToSubmit; i++) {
        const user = validUserBatch[i];
        const percentage = Math.round(((i + 1) / totalToSubmit) * 100);
        
        progressBar.style.width = `${percentage}%`;
        progressBar.textContent = `${percentage}%`;
        progressBar.setAttribute("aria-valuenow", percentage);
        progressText.textContent = `Processing ${i + 1} / ${totalToSubmit}`;

        try {
          const response = await fetch("/email/create_single", {
            method: "POST",
            headers: { "Content-Type": "application/json", "X-Requested-With": "XMLHttpRequest" },
            body: JSON.stringify(user),
          });

          const result = await response.json();
          const userInBatch = userBatch.find(u => u.email === user.email);

          if (response.ok && result.success) {
            successCount++;
            logResult(user.email, "SUCCESS", "Account created successfully.");
            if(userInBatch) userInBatch.status = "created";
          } else {
            failureCount++;
            logResult(user.email, "FAILURE", result.message || "An unknown error occurred.");
            if(userInBatch) {
              userInBatch.status = "failed";
              userInBatch.errorMessage = result.message;
            }
          }
        } catch (error) {
          failureCount++;
          logResult(user.email, "FAILURE", "A network or server error occurred.");
          const userInBatch = userBatch.find(u => u.email === user.email);
          if(userInBatch) {
              userInBatch.status = "failed";
              userInBatch.errorMessage = "A network or server error occurred.";
          }
        }
      }

      if (failureCount > 0) {
          userBatch = userBatch.filter(user => user.status === "failed");
          renderResults(userBatch);
          alert(`Batch submission completed with ${failureCount} errors. Please review the statuses and logs, edit passwords for failed entries if needed, and click "Submit Batch" again.`);
          progressSection.style.display = "none";
      } else {
          renderResults(userBatch);
          alert(`Successfully created all ${successCount} email accounts!`);
          setTimeout(() => { window.location.href = "/email"; }, 1000);
      }
    } finally {
      submitBtn.innerHTML = `<i class="fas fa-check-circle me-2"></i>Submit Batch`;
      if (failureCount > 0) {
          updateSubmitButtonState();
      }
    }
  });

  function logResult(email, status, message) {
    const statusColor = status === "SUCCESS" ? "text-success" : "text-danger";
    const logEntry = `<div>[<span class="${statusColor}">${status}</span>] ${email}: ${message}</div>`;
    resultsLog.insertAdjacentHTML("beforeend", logEntry);
    resultsLog.scrollTop = resultsLog.scrollHeight;
  }

  function generateEmail(name) {
    const domain = "@sinjaikab.go.id";
    const maxUsernameLength = 30 - domain.length;
    const username = name.toLowerCase().replace(/\s+/g, "").replace(/[,.]/g, "").substring(0, maxUsernameLength);
    return { username: username, email: `${username}${domain}` };
  }

  function generatePassword(name) {
    const day = new Date().getDate();
    const namePart = name.replace(/\s+/g, "").substring(0, 5).toLowerCase();
    if (!namePart) return `@${day}#`;
    const capitalizedNamePart = namePart.charAt(0).toUpperCase() + namePart.slice(1);
    return `${capitalizedNamePart}@${day}#`;
  }

  function getNikNipPart(nikNip) {
    if (typeof nikNip !== 'string' || nikNip.length < 6) return '';
    const length = nikNip.length;
    const startIndex = length - 6;
    return nikNip.substring(startIndex, startIndex + 2);
  }

  async function checkEmailAvailability(email) {
    try {
      const response = await fetch("/user/check_email", {
        method: "POST",
        headers: { "Content-Type": "application/json", "X-Requested-With": "XMLHttpRequest" },
        body: JSON.stringify({ email: email }),
      });
      if (!response.ok) return { available: false, message: "Server error during check." };
      return await response.json();
    } catch (error) {
      console.error("Error checking email availability:", error);
      return { available: false, message: "Network error." };
    }
  }

  function renderResults(userBatch) {
    resultsTableBody.innerHTML = "";
    if (userBatch.length === 0) {
      resultsTableBody.innerHTML = '<tr><td colspan="7" class="text-center">No names entered.</td></tr>';
      return;
    }

    userBatch.forEach((user, index) => {
      let statusBadge;
      if (user.status === "created") {
        statusBadge = '<span class="badge bg-success">Created</span>';
      } else if (user.status === "failed") {
        statusBadge = `<span class="badge bg-danger" title="${user.errorMessage || 'Failed'}">Failed</span>`;
      } else if (user.isDuplicate) {
        statusBadge = '<span class="badge bg-warning text-dark">Duplicate</span>';
      } else if (user.isAvailable) {
        statusBadge = '<span class="badge bg-success">Available</span>';
      } else {
        statusBadge = '<span class="badge bg-danger">Used</span>';
      }

      const isUnitKerjaValid = validUnitKerjaNames.has(user.unitKerja.toLowerCase());
      const unitKerjaCellClass = !isUnitKerjaValid && user.unitKerja !== '' ? 'table-danger' : '';
      const unitKerjaTitle = !isUnitKerjaValid && user.unitKerja !== '' ? 'This Unit Kerja does not exist.' : '';

      const nameCellContent = `<span contenteditable="true" class="editable-name" data-name-index="${index}">${user.name.toUpperCase()}</span>`;
      const emailCellContent = `<span contenteditable="true" class="editable-email" data-email-index="${index}">${user.email}</span>`;
      const passwordCellContent = `<span contenteditable="true" class="editable-password" data-password-index="${index}">${user.password}</span>`;
      const unitKerjaCellContent = `<span contenteditable="true" class="editable-unit-kerja" data-unit-kerja-index="${index}">${user.unitKerja}</span>`;
      
      const row = `
        <tr>
            <td>${index + 1}</td>
            <td>${highlightNikNip(user.nikNip)}</td>
            <td>${nameCellContent}</td>
            <td class="${unitKerjaCellClass}" title="${unitKerjaTitle}">${unitKerjaCellContent}</td>
            <td>${emailCellContent}</td>
            <td>${passwordCellContent}</td>
            <td class="text-center">${statusBadge}</td>
        </tr>`;
      resultsTableBody.insertAdjacentHTML("beforeend", row);

      if (user.status === 'failed' && user.errorMessage) {
        const errorRow = `
          <tr class="error-row" data-index="${index}">
              <td colspan="7" class="py-0">
                  <div class="alert alert-danger mb-0 py-1 px-2 border-0 rounded-0">
                      <i class="fas fa-exclamation-circle me-2"></i>
                      <small>${user.errorMessage}</small>
                  </div>
              </td>
          </tr>`;
        resultsTableBody.insertAdjacentHTML("beforeend", errorRow);
      }
    });

    addEditableListeners();
  }

  function addEditableListeners() {
    document.querySelectorAll(".editable-name, .editable-email, .editable-password, .editable-unit-kerja").forEach(cell => {
      cell.addEventListener("blur", handleCellEdit);
      cell.addEventListener("keydown", e => {
        if (e.key === "Enter") {
          e.preventDefault();
          cell.blur();
        }
      });
    });
  }

  async function handleCellEdit(event) {
    const editedCell = event.target;
    const index = parseInt(editedCell.dataset.nameIndex || editedCell.dataset.emailIndex || editedCell.dataset.passwordIndex || editedCell.dataset.unitKerjaIndex);
    const user = userBatch[index];
    const newContent = editedCell.textContent.trim();

    if (editedCell.classList.contains('editable-name')) {
        if (newContent === user.name) return;
        user.name = newContent;
        const { username, email } = generateEmail(newContent);
        user.generatedUsername = username;
        user.email = email;
        user.password = generatePassword(newContent);
    } else if (editedCell.classList.contains('editable-email')) {
        if (newContent === user.email) return;
        if (!isValidEmail(newContent)) {
            alert("Invalid email format.");
            editedCell.textContent = user.email;
            return;
        }
        user.email = newContent;
    } else if (editedCell.classList.contains('editable-password')) {
        if (newContent === user.password) return;
        user.password = newContent;
        user.status = "pending";
        renderResults(userBatch);
        updateSubmitButtonState();
        return;
    } else if (editedCell.classList.contains('editable-unit-kerja')) {
        if (newContent === user.unitKerja) return;
        user.unitKerja = newContent;
        renderResults(userBatch);
        updateSubmitButtonState();
        return;
    }

    const statusCell = editedCell.closest("tr").cells[6];
    statusCell.innerHTML = '<span class="badge bg-info">Re-checking...</span>';
    const result = await checkEmailAvailability(user.email);
    user.isAvailable = result.available;
    user.isDuplicate = userBatch.some((u, i) => u.email === user.email && i !== index);
    if (user.isDuplicate) user.isAvailable = false;
    
    renderResults(userBatch);
    updateSubmitButtonState();
  }

  function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
  }

  function updateSubmitButtonState() {
    const hasInvalidUnitKerja = userBatch.some(user => 
        user.status !== "created" && 
        !validUnitKerjaNames.has(user.unitKerja.toLowerCase())
    );

    validUserBatch = userBatch.filter(user => 
        !user.isDuplicate && 
        user.isAvailable && 
        user.status !== "created" &&
        validUnitKerjaNames.has(user.unitKerja.toLowerCase())
    );
    
    const hasProblematicPendingEmails = userBatch.some(user => user.status !== "created" && (!user.isAvailable || user.isDuplicate));
    
    submitBtn.disabled = validUserBatch.length === 0 || hasProblematicPendingEmails || hasInvalidUnitKerja;
  }

  function highlightNikNip(nikNip) {
    if (typeof nikNip !== 'string' || nikNip.length < 6) return nikNip;
    const length = nikNip.length;
    const highlightStartIndex = length - 6;
    const beforeHighlight = nikNip.substring(0, highlightStartIndex);
    const highlightChars = nikNip.substring(highlightStartIndex, highlightStartIndex + 2);
    const afterHighlight = nikNip.substring(highlightStartIndex + 2);
    return `${beforeHighlight}<span style="background-color: yellow; font-weight: bold;">${highlightChars}</span>${afterHighlight}`;
  }
});
