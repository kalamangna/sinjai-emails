document.addEventListener("DOMContentLoaded", function () {
  const nameInput = document.getElementById("name_input");
  const nikInput = document.getElementById("nik_input");
  const nipInput = document.getElementById("nip_input");
  const jenisFormasiInput = document.getElementById("status_asn_input");

  const unitKerjaInputSingle = document.getElementById(
    "unit_kerja_input_single",
  );

  const generateBtn = document.getElementById("generate_btn");
  const resultsTableBody = document.querySelector("#results_table tbody");
  const submitBtn = document.getElementById("submit_btn");

  const progressSection = document.getElementById("progress_section");
  const progressBar = document.getElementById("progress_bar");
  const progressText = document.getElementById("progress_text");
  const resultsLog = document.getElementById("results_log");

  let validUserBatch = [];
  let userBatch = [];

  const validUnitKerjaNames = new Set(
    unitKerjaOptions.map((option) => option.nama_unit_kerja.toLowerCase()),
  );

  generateBtn.addEventListener("click", async function () {
    const names = nameInput.value.split("\n").map((n) => n.trim());
    const niks = nikInput.value.split("\n").map((n) => n.trim());
    const nips = nipInput.value.split("\n").map((n) => n.trim());

    // Filter out rows where all relevant fields (name, nik, nip) are empty
    const maxLines = Math.max(names.length, niks.length, nips.length);
    const filteredRows = [];

    for (let i = 0; i < maxLines; i++) {
      const name = names[i] || "";
      const nik = niks[i] || "";
      const nip = nips[i] || "";

      if (name !== "" || nik !== "" || nip !== "") {
        filteredRows.push({ name, nik, nip });
      }
    }

    if (filteredRows.length === 0) {
      alert("Please enter data for at least one record.");
      return;
    }

    const finalNames = filteredRows.map((r) => r.name);
    const finalNiks = filteredRows.map((r) => r.nik);
    const finalNips = filteredRows.map((r) => r.nip);

    let unitKerjaValues = [];
    let validationError = "";

    const singleUnitKerja = unitKerjaInputSingle.value;

    if (finalNames.some((n) => n === ""))
      validationError = "One or more rows are missing a name.";
    else if (finalNips.some((n) => n === ""))
      validationError = "One or more rows are missing a NIP.";
    else if (!jenisFormasiInput.value)
      validationError = "Please select a Status ASN.";
    else if (!singleUnitKerja) validationError = "Please select a Unit Kerja.";

    if (!validationError) {
      for (let i = 0; i < filteredRows.length; i++) {
        unitKerjaValues.push(singleUnitKerja);
      }
    }

    if (validationError) {
      alert(validationError);
      return;
    }

    generateBtn.disabled = true;
    generateBtn.innerHTML = `<i class="fas fa-spinner fa-spin mr-2"></i> Memproses...`;
    submitBtn.disabled = true;
    resultsTableBody.innerHTML =
      '<tr><td colspan="8" class="px-10 py-12 text-center text-blue-600 font-bold uppercase tracking-widest text-[10px] animate-pulse">Sedang memproses dan memeriksa email...</td></tr>';

    const trimmedNiks = finalNiks;
    const trimmedNips = finalNips;

    const nikCounts = {};
    for (const nik of trimmedNiks) {
      if (nik) nikCounts[nik] = (nikCounts[nik] || 0) + 1;
    }
    const nipCounts = {};
    for (const nip of trimmedNips) {
      nipCounts[nip] = (nipCounts[nip] || 0) + 1;
    }

    const generatedEmails = new Set();
    userBatch = [];
    for (let i = 0; i < filteredRows.length; i++) {
      const name = finalNames[i];
      const cleanedName = name.replace(/[,.']/g, "");
      const nik = trimmedNiks[i] || "";
      const nip = trimmedNips[i] || ""; // New
      const jenisFormasi = jenisFormasiInput.value; // New
      const unitKerja = unitKerjaValues[i];
      const password = generatePassword(cleanedName, nip);
      const { username: originalUsername, email: originalEmail } =
        generateEmail(cleanedName);

      let currentUsername = originalUsername;
      let currentEmail = originalEmail;
      let isAvailable = false;
      let attempts = 0;
      const maxAttempts = 10;

      while (attempts < maxAttempts) {
        attempts++;

        let suffix = "";
        if (attempts === 1) suffix = getNipPart(nip);
        else if (attempts === 2) suffix = getSecondNipPart(nip);
        else if (attempts === 3) suffix = getNikPart(nik);
        else {
          let base = getNipPart(nip) || getNikPart(nik);
          suffix = (base || "") + attempts;
        }
        if (!suffix) suffix = attempts;

        if (generatedEmails.has(currentEmail)) {
          currentUsername = `${originalUsername}${suffix}`;
          currentEmail = `${currentUsername}@sinjaikab.go.id`;
          continue;
        }

        const result = await checkEmailAvailability(currentEmail);
        if (result.available) {
          isAvailable = true;
          break;
        } else {
          currentUsername = `${originalUsername}${suffix}`;
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
        jabatan: "", // Removed from input but kept in object for model safety if needed
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
    generateBtn.innerHTML = `<i class="fas fa-eye mr-2 text-white/80"></i> Preview`;
    updateSubmitButtonState();
  });

  submitBtn.addEventListener("click", async function () {
    if (validUserBatch.length === 0) {
      alert("No valid emails to submit.");
      return;
    }

    submitBtn.disabled = true;
    submitBtn.innerHTML = `<i class="fas fa-spinner fa-spin mr-2"></i> Memproses...`;
    progressSection.style.display = "block";
    progressSection.scrollIntoView({ behavior: "smooth", block: "center" });
    resultsLog.innerHTML = "";

    const totalToSubmit = validUserBatch.length;
    let successCount = 0;
    let failureCount = 0;

    try {
      for (let i = 0; i < totalToSubmit; i++) {
        const user = validUserBatch[i];
        const percentage = Math.round(((i + 1) / totalToSubmit) * 100);

        progressBar.style.width = `${percentage}%`;
        progressBar.setAttribute("aria-valuenow", percentage);
        progressText.textContent = `${percentage}% (Processing ${i + 1} / ${totalToSubmit})`;

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
            const errorMsg = result.message || "An unknown error occurred.";
            failureCount++;
            
            if (userInBatch) {
              userInBatch.status = "failed";
              
              // Detect if failure is because password too weak
              if (errorMsg.toLowerCase().includes('strength') || errorMsg.toLowerCase().includes('weak')) {
                // Change password to use 7th & 8th digit of NIP
                const strongerPassword = generatePassword(user.name, user.nip, true);
                userInBatch.password = strongerPassword;
                userInBatch.errorMessage = "Password terlalu lemah. Sistem telah memperbarui password menggunakan digit NIP alternatif. Silakan klik Eksekusi lagi.";
                logResult(user.email, "WEAK PW", "Password too weak. System changed to alternative NIP digits.");
              } else {
                userInBatch.errorMessage = errorMsg;
                logResult(user.email, "FAILURE", errorMsg);
              }
            }
          }
        } catch (error) {
          failureCount++;
          logResult(
            user.email,
            "FAILURE",
            "A network or server error occurred.",
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
          `Batch selesai dengan ${failureCount} kesalahan. Silakan periksa log. Untuk kesalahan password lemah, password sudah diperbarui otomatis, silakan klik "Eksekusi" kembali.`,
        );
        progressSection.style.display = "none";
      } else {
        renderResults(userBatch);
        alert(`Berhasil membuat ${successCount} akun email!`);
        setTimeout(() => {
          window.location.href = "/email";
        }, 1000);
      }
    } finally {
      submitBtn.innerHTML = `<i class="fas fa-cloud-upload-alt mr-2 text-white/80"></i> Eksekusi`;
      if (failureCount > 0) {
        updateSubmitButtonState();
      }
    }
  });

  function logResult(email, status, message) {
    const statusColor = status === "SUCCESS" ? "text-emerald-500" : (status === "WEAK PW" ? "text-amber-500" : "text-red-500");
    const logEntry = `<div>[<span class="${statusColor} font-bold">${status}</span>] ${email}: ${message}</div>`;
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

  function generatePassword(name, nip, useAltNipPart = false) {
    let suffix = new Date().getDate();
    if (nip && nip.length >= 8) {
      if (useAltNipPart) {
        suffix = nip.substring(6, 8); // 7th & 8th
      } else {
        suffix = nip.substring(2, 4); // 3rd & 4th
      }
    } else if (nip && nip.length >= 4) {
        suffix = nip.substring(2, 4);
    }

    const namePart = name.replace(/\s+/g, "").substring(0, 5).toLowerCase();
    if (!namePart) return `@${suffix}#`;
    const capitalizedNamePart =
      namePart.charAt(0).toUpperCase() + namePart.slice(1);
    return `${capitalizedNamePart}@${suffix}#`;
  }

  function getSecondNipPart(nip) {
    if (typeof nip !== "string" || nip.length < 8) return "";
    return nip.substring(6, 8);
  }

  function getNipPart(nip) {
    if (typeof nip !== "string" || nip.length < 4) return "";
    return nip.substring(2, 4);
  }

  function getNikPart(nik) {
    if (typeof nik !== "string" || nik.length < 8) return "";
    return nik.substring(10, 12);
  }

  async function checkNikOnServer(nik) {
    if (!nik) return { exists: false };
    try {
      const response = await fetch("/user/check_niknip", {
        // Updated endpoint
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

  async function checkNipOnServer(nip) {
    // New function for NIP check
    if (!nip) return { exists: false };
    try {
      const response = await fetch("/user/check_niknip", {
        // Using the same endpoint, backend needs to handle both
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
        '<tr><td colspan="8" class="px-10 py-12 text-center text-slate-400 font-medium uppercase tracking-widest text-[10px]">No names entered.</td></tr>';
      return;
    }

    userBatch.forEach((user, index) => {
      let statusBadge;
      const badgeBase =
        "inline-flex items-center px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest border shadow-sm";

      if (user.status === "created") {
        statusBadge = `<span class="${badgeBase} bg-emerald-50 text-emerald-600 border-emerald-200">Created</span>`;
      } else if (user.status === "failed") {
        statusBadge = `<span class="${badgeBase} bg-red-50 text-red-600 border-red-200" title="${user.errorMessage || "Failed"}">Failed</span>`;
      } else if (user.isDuplicate) {
        statusBadge = `<span class="${badgeBase} bg-amber-50 text-amber-600 border-amber-200">Duplicate</span>`;
      } else if (user.isAvailable) {
        statusBadge = `<span class="${badgeBase} bg-blue-50 text-blue-600 border-blue-200">Available</span>`;
      } else {
        statusBadge = `<span class="${badgeBase} bg-slate-50 text-slate-700 border-slate-200">Used</span>`;
      }

      const nameCellContent = `<span contenteditable="true" class="editable-name focus:outline-none focus:text-blue-600 transition-colors" data-name-index="${index}">${user.name}</span>`;

      const domain = "@sinjaikab.go.id";
      const username = user.email.substring(0, user.email.indexOf(domain));
      const emailCellContent = `<span contenteditable="true" class="editable-username focus:outline-none focus:text-blue-600 transition-colors" data-username-index="${index}">${username}</span><span class="text-slate-200 font-medium">${domain}</span>`;

      const passwordCellContent = `<span contenteditable="true" class="editable-password font-mono focus:outline-none focus:text-blue-600 transition-colors" data-password-index="${index}">${user.password}</span>`;
      const unitKerjaCellContent = `<span class="editable-unit-kerja opacity-80" data-unit-kerja-index="${index}">${user.unitKerja}</span>`;

      const tagBase =
        "ml-1.5 px-1.5 py-0.5 rounded text-[8px] font-black uppercase";

      let nikDisplay = `<span class="font-mono text-slate-700">${user.nik || "-"}</span>`;
      if (user.isNikInDb) {
        nikDisplay += `<span class="${tagBase} bg-red-50 text-red-600" title="NIK already exists in the database">DB</span>`;
      }
      if (user.isNikDuplicate) {
        nikDisplay += `<span class="${tagBase} bg-amber-50 text-amber-600" title="Duplicate NIK in this batch">DUP</span>`;
      }

      let nipDisplay = `<span class="font-mono text-slate-700">${user.nip || "-"}</span>`;
      if (user.isNipInDb) {
        nipDisplay += `<span class="${tagBase} bg-red-50 text-red-600" title="NIP already exists in the database">DB</span>`;
      }
      if (user.isNipDuplicate) {
        nipDisplay += `<span class="${tagBase} bg-amber-50 text-amber-600" title="Duplicate NIP in this batch">DUP</span>`;
      }

      const row = `
        <tr class="hover:bg-slate-50 transition-colors group">
            <td class="px-6 py-5 whitespace-nowrap text-[10px] font-black text-slate-700 font-mono">#${index + 1}</td>
            <td class="px-6 py-5 whitespace-nowrap">${nipDisplay}</td>
            <td class="px-6 py-5 whitespace-nowrap">${nikDisplay}</td>
            <td class="px-6 py-5 font-black text-slate-800 tracking-tight whitespace-nowrap">${nameCellContent}</td>
            <td class="px-6 py-5 whitespace-nowrap font-bold text-slate-700 tracking-tight lowercase">${emailCellContent}</td>
            <td class="px-6 py-5 whitespace-nowrap">${passwordCellContent}</td>
            <td class="px-6 py-5 text-center whitespace-nowrap">${statusBadge}</td>
        </tr>`;
      resultsTableBody.insertAdjacentHTML("beforeend", row);

      if (user.status === "failed" && user.errorMessage) {
        const errorRow = `
          <tr class="error-row" data-index="${index}">
              <td colspan="8" class="px-6 py-0">
                  <div class="bg-red-50 text-red-600 px-4 py-2 border-x border-red-200 flex items-center">
                      <i class="fas fa-exclamation-circle mr-3 text-xs opacity-50"></i>
                      <span class="text-[10px] font-black uppercase tracking-widest">${user.errorMessage}</span>
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
        ".editable-name, .editable-username, .editable-password, .editable-nip",
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
        editedCell.dataset.nipIndex,
    );
    const user = userBatch[index];
    const newContent = editedCell.textContent.trim();

    if (editedCell.classList.contains("editable-name")) {
      if (newContent === user.name) return;
      user.name = newContent;
      const { username, email } = generateEmail(newContent);
      user.generatedUsername = username;
      user.email = email;
      user.password = generatePassword(newContent, user.nip);
    } else if (editedCell.classList.contains("editable-nip")) {
      if (newContent === user.nip) return;
      user.nip = newContent;
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
    }

    const statusCell = editedCell.closest("tr").cells[7];
    statusCell.innerHTML = '<span class="badge bg-info">Re-checking...</span>';
    const result = await checkEmailAvailability(user.email);
    user.isAvailable = result.available;
    user.isDuplicate = userBatch.some(
      (u, i) => u.email === user.email && i !== index,
    );
    if (user.isDuplicate) user.isAvailable = false;

    renderResults(userBatch);
    updateSubmitButtonState();
  }

  function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
  }

  function updateSubmitButtonState() {
    const hasNikDuplicates = userBatch.some(
      (user) => user.status !== "created" && user.isNikDuplicate,
    );
    const hasNikInDb = userBatch.some(
      (user) => user.status !== "created" && user.isNikInDb,
    );
    const hasNipDuplicates = userBatch.some(
      // New
      (user) => user.status !== "created" && user.isNipDuplicate,
    );
    const hasNipInDb = userBatch.some(
      // New
      (user) => user.status !== "created" && user.isNipInDb,
    );

    validUserBatch = userBatch.filter(
      (user) =>
        !user.isDuplicate &&
        !user.isNikDuplicate &&
        !user.isNikInDb &&
        !user.isNipDuplicate && // New
        !user.isNipInDb && // New
        user.isAvailable &&
        user.status !== "created",
    );

    const hasProblematicPendingEmails = userBatch.some(
      (user) =>
        user.status !== "created" && (!user.isAvailable || user.isDuplicate),
    );

    submitBtn.disabled =
      validUserBatch.length === 0 ||
      hasProblematicPendingEmails ||
      hasNikDuplicates ||
      hasNikInDb ||
      hasNipDuplicates || // New
      hasNipInDb; // New
  }
});
