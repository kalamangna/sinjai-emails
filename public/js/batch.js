document.addEventListener("DOMContentLoaded", function () {
  const nameInput = document.getElementById("name_input");
  const nikInput = document.getElementById("nik_input");
  const nipInput = document.getElementById("nip_input"); // New
  const jabatanInput = document.getElementById("jabatan_input"); // New
  const jenisFormasiInput = document.getElementById("jenis_formasi_input"); // New

  const modeSingleRadio = document.getElementById("mode_single");
  const modeMultipleRadio = document.getElementById("mode_multiple");
  const singleUnitKerjaWrapper = document.getElementById(
    "single_unit_kerja_wrapper"
  );
  const multipleUnitKerjaWrapper = document.getElementById(
    "multiple_unit_kerja_wrapper"
  );
  const unitKerjaInputSingle = document.getElementById(
    "unit_kerja_input_single"
  );
  const unitKerjaInputMultiple = document.getElementById(
    "unit_kerja_input_multiple"
  );

  const generateBtn = document.getElementById("generate_btn");
  const resultsTableBody = document.querySelector("#results_table tbody");
  const submitBtn = document.getElementById("submit_btn");

  const progressSection = document.getElementById("progress_section");
  const progressBar = document.getElementById("progress_bar");
  const progressText = document.getElementById("progress_text");
  const resultsLog = document.getElementById("results_log");

  // CSV Import Elements
  const importCsvBtn = document.getElementById('import_csv_btn');
  const csvFileInput = document.getElementById('csv_file_input');
  const downloadTemplateBtn = document.getElementById('download_template_btn');

  if (importCsvBtn && csvFileInput && downloadTemplateBtn) {
      importCsvBtn.addEventListener('click', () => csvFileInput.click());

      downloadTemplateBtn.addEventListener('click', (e) => {
          e.preventDefault();
          const headers = ['name', 'nik', 'nip', 'jabatan', 'unit_kerja', 'status_asn'];
          const csvContent = headers.join(',') + '\n';
          const blob = new Blob([csvContent], { type: 'text/csv' });
          const url = window.URL.createObjectURL(blob);
          const a = document.createElement('a');
          a.href = url;
          a.download = 'batch_create_template.csv';
          document.body.appendChild(a);
          a.click();
          document.body.removeChild(a);
      });

      csvFileInput.addEventListener('change', (e) => {
          const file = e.target.files[0];
          if (!file) return;

          const reader = new FileReader();
          reader.onload = (event) => {
              const text = event.target.result;
              const data = parseCSV(text);
              populateFields(data);
              csvFileInput.value = ''; // Reset
          };
          reader.readAsText(file);
      });
  }

  function parseCSV(text) {
      // Normalize line endings
      const normalizedText = text.replace(/\r\n/g, '\n').replace(/\r/g, '\n');
      const lines = normalizedText.split('\n').filter(l => l.trim() !== '');
      if (lines.length < 2) return []; // No data

      // Detect delimiter
      const firstLine = lines[0];
      const commaCount = (firstLine.match(/,/g) || []).length;
      const semicolonCount = (firstLine.match(/;/g) || []).length;
      const delimiter = semicolonCount > commaCount ? ';' : ',';

      const headers = lines[0].split(delimiter).map(h => h.trim().toLowerCase().replace(/^"|"$/g, ''));
      const result = [];

      for (let i = 1; i < lines.length; i++) {
          // Simple CSV split (doesn't handle complex quoted strings with delimiters inside)
          const currentLine = lines[i].split(delimiter); 
          const obj = {};
          
          headers.forEach((header, index) => {
              let value = currentLine[index] ? currentLine[index].trim().replace(/^"|"$/g, '') : '';
              obj[header] = value;
          });
          result.push(obj);
      }
      return result;
  }

  function populateFields(data) {
      if (data.length === 0) return;

      const inputs = [
          { input: nameInput, keys: ['name', 'nama'] },
          { input: nikInput, keys: ['nik'] },
          { input: nipInput, keys: ['nip'] },
          { input: jabatanInput, keys: ['jabatan'] },
          { input: jenisFormasiInput, keys: ['jenis_formasi', 'status_asn'] }
      ];

      // Reset inputs
      inputs.forEach(item => item.input.value = '');

      const values = {};
      inputs.forEach(item => values[item.input.id] = []);

      let unitKerjaFound = false;

      data.forEach(row => {
          inputs.forEach(item => {
              let val = '';
              for (const key of item.keys) {
                  if (row[key] !== undefined) {
                      val = row[key];
                      break;
                  }
              }
              values[item.input.id].push(val);
              if (item.keys.includes('unit_kerja') && val) {
                  unitKerjaFound = true;
              }
          });
      });

      inputs.forEach(item => {
          item.input.value = values[item.input.id].join('\n');
      });

      // Special handling for select inputs like jenisFormasiInput
      const jenisFormasiValue = values[jenisFormasiInput.id].length > 0 ? values[jenisFormasiInput.id][0] : '';
      if (jenisFormasiInput && jenisFormasiValue) {
          jenisFormasiInput.value = jenisFormasiValue;
      }

      if (unitKerjaFound) {
          document.getElementById('mode_multiple').checked = true;
          document.getElementById('mode_multiple').dispatchEvent(new Event('change'));
      }

      alert(`Imported ${data.length} rows successfully.`);
  }

  let validUserBatch = [];
  let userBatch = [];

  const validUnitKerjaNames = new Set(
    unitKerjaOptions.map((option) => option.nama_unit_kerja.toLowerCase())
  );

  modeSingleRadio.addEventListener("change", () => {
    singleUnitKerjaWrapper.style.display = "block";
    multipleUnitKerjaWrapper.style.display = "none";
  });

  modeMultipleRadio.addEventListener("change", () => {
    singleUnitKerjaWrapper.style.display = "none";
    multipleUnitKerjaWrapper.style.display = "block";
  });

  generateBtn.addEventListener("click", async function () {
    const names = nameInput
      .value.trim()
      .split("\n")
      .filter((name) => name.trim() !== "");
    const niks = nikInput
      .value.trim()
      .split("\n")
      .filter((nik) => nik.trim() !== "");
    const nips = nipInput // New
      .value.trim()
      .split("\n")
      .filter((nip) => nip.trim() !== "");
    const jabatans = jabatanInput // New
      .value.trim()
      .split("\n")
      .filter((jabatan) => jabatan.trim() !== "");

    const mode = document.querySelector(
      'input[name="unitKerjaMode"]:checked'
    ).value;

    let unitKerjaValues = [];
    let validationError = "";

    if (names.length === 0) validationError = "Please enter at least one name.";
    else if (niks.length === 0)
      validationError = "Please enter at least one NIK.";
    else if (names.length !== niks.length)
      validationError = "The number of names and NIKs must match.";
    else if (nips.length > 0 && nips.length !== names.length) // New validation
      validationError = "The number of NIPs must match the number of names and NIKs.";
    else if (jabatans.length > 0 && jabatans.length !== names.length) // New validation
      validationError = "The number of Jabatans must match the number of names and NIKs.";


    if (mode === "single") {
      const singleUnitKerja = unitKerjaInputSingle.value;
      if (!singleUnitKerja) validationError = "Please select a Unit Kerja.";
      else {
        for (let i = 0; i < names.length; i++)
          unitKerjaValues.push(singleUnitKerja);
      }
    } else {
      unitKerjaValues = unitKerjaInputMultiple.value
        .trim()
        .split("\n")
        .filter((uk) => uk.trim() !== "");
      if (
        unitKerjaValues.length > 0 &&
        unitKerjaValues.length !== names.length
      ) {
        validationError =
          "The number of Unit Kerja entries must match the number of names and NIK/NIPs.";
      }
      if (unitKerjaValues.length === 0) {
        for (let i = 0; i < names.length; i++) unitKerjaValues.push("");
      }
    }

    if (validationError) {
      alert(validationError);
      return;
    }

    generateBtn.disabled = true;
    generateBtn.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Generating...`;
    submitBtn.disabled = true;
    resultsTableBody.innerHTML =
      '<tr><td colspan="9" class="text-center">Generating and checking emails...</td></tr>'; // Updated colspan

    const trimmedNiks = niks.map((n) => n.trim());
    const trimmedNips = nips.map((n) => n.trim()); // New
    const trimmedJabatans = jabatans.map((j) => j.trim()); // New

    const nikCounts = {};
    for (const nik of trimmedNiks) {
      nikCounts[nik] = (nikCounts[nik] || 0) + 1;
    }
    const nipCounts = {}; // New
    for (const nip of trimmedNips) { // New
      nipCounts[nip] = (nipCounts[nip] || 0) + 1;
    }


    const generatedEmails = new Set();
    userBatch = [];
    for (let i = 0; i < names.length; i++) {
      const name = names[i];
      const cleanedName = name.replace(/[,.']/g, "");
      const nik = trimmedNiks[i];
      const nip = trimmedNips[i] || ""; // New
      const jabatan = trimmedJabatans[i] || ""; // New
      const jenisFormasi = jenisFormasiInput.value; // New
      const unitKerja = unitKerjaValues[i];
      const password = generatePassword(cleanedName);
      const { username: originalUsername, email: originalEmail } =
        generateEmail(cleanedName);

      let currentUsername = originalUsername;
      let currentEmail = originalEmail;
      let isAvailable = false;
      let attempts = 0;
      const maxAttempts = 10;

      while (attempts < maxAttempts) {
        attempts++;
        if (generatedEmails.has(currentEmail)) {
          const nikPart = getNikPart(nik);
          currentUsername = `${originalUsername}${nikPart}`;
          currentEmail = `${currentUsername}@sinjaikab.go.id`;
          continue;
        }

        const result = await checkEmailAvailability(currentEmail);
        if (result.available) {
          isAvailable = true;
          break;
        } else {
          const nikPart = getNikPart(nik);
          currentUsername = `${originalUsername}${nikPart}`;
          currentEmail = `${currentUsername}@sinjaikab.go.id`;
        }
      }

      const isDuplicate = generatedEmails.has(currentEmail);
      if (isDuplicate) isAvailable = false;

      const isNikDuplicate = nikCounts[nik] > 1;
      const nikCheckResult = await checkNikOnServer(nik);
      const isNikInDb = nikCheckResult.exists;

      const isNipDuplicate = nipCounts[nip] > 1; // New
      const nipCheckResult = await checkNipOnServer(nip); // New
      const isNipInDb = nipCheckResult.exists; // New


      userBatch.push({
        name: cleanedName.trim(),
        nik: nik,
        nip: nip, // New
        jabatan: jabatan, // New
        jenisFormasi: jenisFormasi, // New
        unitKerja: unitKerja.trim(),
        generatedUsername: currentUsername,
        email: currentEmail,
        password: password,
        quota: 1024,
        isDuplicate: isDuplicate,
        isNikDuplicate: isNikDuplicate,
        isNikInDb: isNikInDb,
        isNipDuplicate: isNipDuplicate, // New
        isNipInDb: isNipInDb, // New
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
            headers: {
              "Content-Type": "application/json",
              "X-Requested-With": "XMLHttpRequest",
            },
            body: JSON.stringify(user),
          });

          const result = await response.json();
          const userInBatch = userBatch.find((u) => u.email === user.email);

          if (response.ok && result.success) {
            successCount++;
            logResult(user.email, "SUCCESS", "Account created successfully.");
            if (userInBatch) userInBatch.status = "created";
          } else {
            failureCount++;
            logResult(
              user.email,
              "FAILURE",
              result.message || "An unknown error occurred."
            );
            if (userInBatch) {
              userInBatch.status = "failed";
              userInBatch.errorMessage = result.message;
            }
          }
        } catch (error) {
          failureCount++;
          logResult(
            user.email,
            "FAILURE",
            "A network or server error occurred."
          );
          const userInBatch = userBatch.find((u) => u.email === user.email);
          if (userInBatch) {
            userInBatch.status = "failed";
            userInBatch.errorMessage = "A network or server error occurred.";
          }
        }
      }

      if (failureCount > 0) {
        userBatch = userBatch.filter((user) => user.status === "failed");
        renderResults(userBatch);
        alert(
          `Batch submission completed with ${failureCount} errors. Please review the statuses and logs, edit passwords for failed entries if needed, and click "Submit Batch" again.`
        );
        progressSection.style.display = "none";
      } else {
        renderResults(userBatch);
        alert(`Successfully created all ${successCount} email accounts!`);
        setTimeout(() => {
          window.location.href = "/email";
        }, 1000);
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
    const username = name
      .toLowerCase()
      .replace(/\s+/g, "")
      .replace(/[,.]/g, "")
      .substring(0, maxUsernameLength);
    return { username: username, email: `${username}${domain}` };
  }

  function generatePassword(name) {
    const day = new Date().getDate();
    const namePart = name.replace(/\s+/g, "").substring(0, 5).toLowerCase();
    if (!namePart) return `@${day}#`;
    const capitalizedNamePart =
      namePart.charAt(0).toUpperCase() + namePart.slice(1);
    return `${capitalizedNamePart}@${day}#`;
  }

  function getNikPart(nik) {
    if (typeof nik !== "string" || nik.length < 8) return "";
    return nik.substring(10, 12);
  }

  async function checkNikOnServer(nik) {
    if (!nik) return { exists: false };
    try {
      const response = await fetch("/user/check_niknip", { // Updated endpoint
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-Requested-With": "XMLHttpRequest",
        },
        body: JSON.stringify({ nik: nik }),
      });
      if (!response.ok)
        return { exists: true, message: "Server error during check." };
      return await response.json();
    } catch (error) {
      console.error("Error checking NIK on server:", error);
      return { exists: true, message: "Network error." };
    }
  }

  async function checkNipOnServer(nip) { // New function for NIP check
    if (!nip) return { exists: false };
    try {
      const response = await fetch("/user/check_niknip", { // Using the same endpoint, backend needs to handle both
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-Requested-With": "XMLHttpRequest",
        },
        body: JSON.stringify({ nip: nip }),
      });
      if (!response.ok)
        return { exists: true, message: "Server error during check." };
      return await response.json();
    } catch (error) {
      console.error("Error checking NIP on server:", error);
      return { exists: true, message: "Network error." };
    }
  }

  async function checkEmailAvailability(email) {
    try {
      const response = await fetch("/user/check_email", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-Requested-With": "XMLHttpRequest",
        },
        body: JSON.stringify({ email: email }),
      });
      if (!response.ok)
        return { available: false, message: "Server error during check." };
      return await response.json();
    } catch (error) {
      console.error("Error checking email availability:", error);
      return { available: false, message: "Network error." };
    }
  }

  function renderResults(userBatch) {
    resultsTableBody.innerHTML = "";
    if (userBatch.length === 0) {
      resultsTableBody.innerHTML =
        '<tr><td colspan="9" class="text-center">No names entered.</td></tr>'; // Updated colspan
      return;
    }

    userBatch.forEach((user, index) => {
      let statusBadge;
      if (user.status === "created") {
        statusBadge = '<span class="badge bg-success">Created</span>';
      } else if (user.status === "failed") {
        statusBadge = `<span class="badge bg-danger" title="${
          user.errorMessage || "Failed"
        }">Failed</span>`;
      } else if (user.isDuplicate) {
        statusBadge =
          '<span class="badge bg-warning text-dark">Duplicate</span>';
      } else if (user.isAvailable) {
        statusBadge = '<span class="badge bg-success">Available</span>';
      } else {
        statusBadge = '<span class="badge bg-danger">Used</span>';
      }

      const isUnitKerjaValid = validUnitKerjaNames.has(
        user.unitKerja.toLowerCase()
      );
      const unitKerjaCellClass =
        !isUnitKerjaValid && user.unitKerja !== "" ? "table-danger" : "";
      const unitKerjaTitle =
        !isUnitKerjaValid && user.unitKerja !== ""
          ? "This Unit Kerja does not exist."
          : "";

      const nameCellContent = `<span contenteditable="true" class="editable-name" data-name-index="${index}">${user.name.toUpperCase()}</span>`;
      const jabatanCellContent = `<span contenteditable="true" class="editable-jabatan" data-jabatan-index="${index}">${user.jabatan}</span>`; // New
      
      const domain = "@sinjaikab.go.id";
      const username = user.email.substring(0, user.email.indexOf(domain));
      const emailCellContent = `<span contenteditable="true" class="editable-username" data-username-index="${index}">${username}</span><span class="text-muted">${domain}</span>`;

      const passwordCellContent = `<span contenteditable="true" class="editable-password" data-password-index="${index}">${user.password}</span>`;
      const unitKerjaCellContent = `<span contenteditable="true" class="editable-unit-kerja" data-unit-kerja-index="${index}">${user.unitKerja}</span>`;

      let nikDisplay = user.nik;
      if (user.isNikInDb) {
        nikDisplay += ` <span class="badge bg-danger" title="NIK already exists in the database">In DB</span>`;
      }
      if (user.isNikDuplicate) {
        nikDisplay += ` <span class="badge bg-warning text-dark" title="Duplicate NIK in this batch">Duplicate</span>`;
      }

      let nipDisplay = user.nip; // New
      if (user.isNipInDb) { // New
        nipDisplay += ` <span class="badge bg-danger" title="NIP already exists in the database">In DB</span>`;
      }
      if (user.isNipDuplicate) { // New
        nipDisplay += ` <span class="badge bg-warning text-dark" title="Duplicate NIP in this batch">Duplicate</span>`;
      }


      const row = `
        <tr>
            <td>${index + 1}</td>
            <td>${nikDisplay}</td>
            <td>${nipDisplay}</td> <!-- New -->
            <td>${nameCellContent}</td>
            <td>${jabatanCellContent}</td> <!-- New -->
            <td class="${unitKerjaCellClass}" title="${unitKerjaTitle}">${unitKerjaCellContent}</td>
            <td>${emailCellContent}</td>
            <td>${passwordCellContent}</td>
            <td class="text-center">${statusBadge}</td>
        </tr>`;
      resultsTableBody.insertAdjacentHTML("beforeend", row);

      if (user.status === "failed" && user.errorMessage) {
        const errorRow = `
          <tr class="error-row" data-index="${index}">
              <td colspan="9" class="py-0"> <!-- Updated colspan -->
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
    document
      .querySelectorAll(
        ".editable-name, .editable-username, .editable-password, .editable-unit-kerja, .editable-jabatan, .editable-nip" // Updated
      )
      .forEach((cell) => {
        cell.addEventListener("blur", handleCellEdit);
        cell.addEventListener("keydown", (e) => {
          if (e.key === "Enter") {
            e.preventDefault();
            cell.blur();
          }
        });
      });
  }

  async function handleCellEdit(event) {
    const editedCell = event.target;
    const index = parseInt(
      editedCell.dataset.nameIndex ||
        editedCell.dataset.usernameIndex ||
        editedCell.dataset.passwordIndex ||
        editedCell.dataset.unitKerjaIndex ||
        editedCell.dataset.jabatanIndex || // New
        editedCell.dataset.nipIndex // New
    );
    const user = userBatch[index];
    const newContent = editedCell.textContent.trim();

    if (editedCell.classList.contains("editable-name")) {
      if (newContent === user.name) return;
      user.name = newContent;
      const { username, email } = generateEmail(newContent);
      user.generatedUsername = username;
      user.email = email;
      user.password = generatePassword(newContent);
    } else if (editedCell.classList.contains("editable-nip")) { // New
      if (newContent === user.nip) return;
      user.nip = newContent;
      renderResults(userBatch); // Re-render to update NIP status badges
      updateSubmitButtonState();
      return;
    } else if (editedCell.classList.contains("editable-jabatan")) { // New
      if (newContent === user.jabatan) return;
      user.jabatan = newContent;
      user.status = "pending";
      renderResults(userBatch);
      updateSubmitButtonState();
      return;
    } else if (editedCell.classList.contains("editable-username")) {
      const domain = "@sinjaikab.go.id";
      const newEmail = newContent + domain;
      if (newEmail === user.email) return;

      user.email = newEmail;
    } else if (editedCell.classList.contains("editable-password")) {
      if (newContent === user.password) return;
      user.password = newContent;
      user.status = "pending";
      renderResults(userBatch);
      updateSubmitButtonState();
      return;
    } else if (editedCell.classList.contains("editable-unit-kerja")) {
      if (newContent === user.unitKerja) return;
      user.unitKerja = newContent;
      renderResults(userBatch);
      updateSubmitButtonState();
      return;
    }

    const statusCell = editedCell.closest("tr").cells[8]; // Updated index
    statusCell.innerHTML = '<span class="badge bg-info">Re-checking...</span>';
    const result = await checkEmailAvailability(user.email);
    user.isAvailable = result.available;
    user.isDuplicate = userBatch.some(
      (u, i) => u.email === user.email && i !== index
    );
    if (user.isDuplicate) user.isAvailable = false;

    renderResults(userBatch);
    updateSubmitButtonState();
  }

  function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
  }

  function updateSubmitButtonState() {
    const hasInvalidUnitKerja = userBatch.some(
      (user) =>
        user.status !== "created" &&
        !validUnitKerjaNames.has(user.unitKerja.toLowerCase())
    );

    const hasNikDuplicates = userBatch.some(
      (user) => user.status !== "created" && user.isNikDuplicate
    );
    const hasNikInDb = userBatch.some(
      (user) => user.status !== "created" && user.isNikInDb
    );
    const hasNipDuplicates = userBatch.some( // New
      (user) => user.status !== "created" && user.isNipDuplicate
    );
    const hasNipInDb = userBatch.some( // New
      (user) => user.status !== "created" && user.isNipInDb
    );


    validUserBatch = userBatch.filter(
      (user) =>
        !user.isDuplicate &&
        !user.isNikDuplicate &&
        !user.isNikInDb &&
        !user.isNipDuplicate && // New
        !user.isNipInDb && // New
        user.isAvailable &&
        user.status !== "created" &&
        validUnitKerjaNames.has(user.unitKerja.toLowerCase())
    );

    const hasProblematicPendingEmails = userBatch.some(
      (user) =>
        user.status !== "created" && (!user.isAvailable || user.isDuplicate)
    );

    submitBtn.disabled =
      validUserBatch.length === 0 ||
      hasProblematicPendingEmails ||
      hasInvalidUnitKerja ||
      hasNikDuplicates ||
      hasNikInDb ||
      hasNipDuplicates || // New
      hasNipInDb; // New
  }
});