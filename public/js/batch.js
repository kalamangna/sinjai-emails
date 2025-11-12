document.addEventListener("DOMContentLoaded", function () {
  const nameInput = document.getElementById("name_input");
  const nikNipInput = document.getElementById("nik_nip_input");
  const unitKerjaInput = document.getElementById("unit_kerja_input");
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

  generateBtn.addEventListener("click", async function () {
    const names = nameInput.value
      .trim()
      .split("\n")
      .filter((name) => name.trim() !== "");
    const nikNips = nikNipInput.value
      .trim()
      .split("\n")
      .filter((nikNip) => nikNip.trim() !== "");
    const unitKerja = unitKerjaInput.value;

    if (names.length === 0 || nikNips.length === 0 || names.length !== nikNips.length || !unitKerja) {
      alert("Please ensure all fields are filled correctly and the number of names and NIK/NIPs match.");
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
        unitKerja: unitKerja,
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
      } else {
          renderResults(userBatch); // Show all as "created" before redirecting
          alert(`Successfully created all ${successCount} email accounts!`);
          setTimeout(() => { window.location.href = "/email"; }, 1000);
      }
    } finally {
      submitBtn.innerHTML = `<i class="fas fa-check-circle me-2"></i>Submit Batch`;
      if (failureCount > 0) { // Only re-evaluate button state if not redirecting
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

      const nameCellContent = `<span contenteditable="true" class="editable-name" data-name-index="${index}">${user.name.toUpperCase()}</span>`;
      const emailCellContent = `<span contenteditable="true" class="editable-email" data-email-index="${index}">${user.email}</span>`;
      const passwordCellContent = `<span contenteditable="true" class="editable-password" data-password-index="${index}">${user.password}</span>`;
      
      const row = `
        <tr>
            <td>${index + 1}</td>
            <td>${highlightNikNip(user.nikNip)}</td>
            <td>${nameCellContent}</td>
            <td>${user.unitKerja}</td>
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
    document.querySelectorAll(".editable-name, .editable-email, .editable-password").forEach(cell => {
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
    const index = parseInt(editedCell.dataset.nameIndex || editedCell.dataset.emailIndex || editedCell.dataset.passwordIndex);
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
        user.status = "pending"; // Mark for re-submission
        renderResults(userBatch);
        updateSubmitButtonState();
        return; // No need to re-check availability for a password change
    }

    // Re-check availability for name or email change
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
    validUserBatch = userBatch.filter(user => !user.isDuplicate && user.isAvailable && user.status !== "created");
    const hasProblematicPendingEmails = userBatch.some(user => user.status !== "created" && (!user.isAvailable || user.isDuplicate));
    submitBtn.disabled = validUserBatch.length === 0 || hasProblematicPendingEmails;
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