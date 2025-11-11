document.addEventListener("DOMContentLoaded", function () {
  const nameInput = document.getElementById("name_input");
  const nikNipInput = document.getElementById("nik_nip_input");
  const unitKerjaInput = document.getElementById("unit_kerja_input");
  const generateBtn = document.getElementById("generate_btn");
  const resultsTableBody = document.querySelector("#results_table tbody");
  const submitBtn = document.getElementById("submit_btn");

  let validUserBatch = [];
  let userBatch = []; // Declared at a higher scope

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

    if (names.length === 0) {
      alert("Please enter at least one name.");
      return;
    }
    if (nikNips.length === 0) {
      alert("Please enter at least one NIK/NIP.");
      return;
    }
    if (names.length !== nikNips.length) {
      alert("The number of names and NIK/NIPs must match.");
      return;
    }
    if (!unitKerja) {
      alert("Please select a Unit Kerja.");
      return;
    }

    // Reset and disable buttons
    validUserBatch = [];
    userBatch = []; // Clear global userBatch for new generation
    generateBtn.disabled = true;
    generateBtn.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Generating...`;
    submitBtn.disabled = true;

    resultsTableBody.innerHTML =
      '<tr><td colspan="7" class="text-center">Generating and checking emails...</td></tr>';

    const generatedEmails = new Set();
    let allEmailsValid = true;

    for (let i = 0; i < names.length; i++) {
      const name = names[i];
      const cleanedName = name.replace(/[,.']/g, ""); // Clean the name
      const nikNip = nikNips[i];
      const { username: generatedUsername, email } = generateEmail(cleanedName); // Use cleaned name
      const password = generatePassword(cleanedName); // Use cleaned name
      const isDuplicate = generatedEmails.has(email);

      if (isDuplicate) {
        allEmailsValid = false;
      }

      generatedEmails.add(email);

      userBatch.push({
        name: cleanedName.trim(), // Store the cleaned name for display
        nikNip: nikNip.trim(),
        unitKerja: unitKerja,
        generatedUsername: generatedUsername,
        email: email,
        password: password,
        quota: 1024, // Default quota
        isDuplicate: isDuplicate,
        isAvailable: false,
        status: "pending", // Initialize status for each user
      });
    }

    const checkPromises = userBatch.map((user) => {
      if (user.isDuplicate) {
        return Promise.resolve(user);
      }
      return checkEmailAvailability(user.email).then((result) => {
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

    updateSubmitButtonState();
  });

  submitBtn.addEventListener("click", async function () {
    if (validUserBatch.length === 0) {
      alert("No valid emails to submit.");
      return;
    }

    submitBtn.disabled = true;
    submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Submitting...`;

    try {
      const response = await fetch("/email/batch_create", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-Requested-With": "XMLHttpRequest",
        },
        body: JSON.stringify(validUserBatch),
      });

      if (!response.ok) {
        throw new Error("Server responded with an error.");
      }

      const result = await response.json();
      handleSubmitResponse(result);
    } catch (error) {
      console.error("Error submitting batch:", error);
      alert("An unexpected error occurred during submission.");
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
    let failureCount = 0;

    // Update the status of each user in the userBatch based on the server response
    result.results.forEach((res) => {
      const user = userBatch.find((u) => u.email === res.email);
      if (user) {
        if (res.success) {
          user.status = "created";
          successCount++;
        } else {
          user.status = "failed";
          user.errorMessage = res.message; // Store the error message
          failureCount++;
        }
      }
    });

    // Re-render the entire table to reflect the new statuses and make passwords editable
    renderResults(userBatch);

    if (failureCount === 0) {
      alert(`Successfully created all ${successCount} email accounts!`);
      setTimeout(() => {
        window.location.href = "/email";
      }, 1000); // Redirect after a short delay
    } else {
      alert(
        `Batch submission completed with ${failureCount} errors. Please review the statuses, edit passwords for failed entries if needed, and click "Submit Batch" again.`
      );
    }

    // After a partial failure, re-evaluate which users are valid for the next submission attempt
    updateSubmitButtonState();
  }

  function generateEmail(name) {
    const domain = "@sinjaikab.go.id";
    const maxUsernameLength = 30 - domain.length;
    const username = name
      .toLowerCase()
      .replace(/\s+/g, "")
      .replace(/[,.]/g, "") // Remove commas and periods
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
      if (!response.ok) {
        return { available: false, message: "Server error during check." };
      }
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
        '<tr><td colspan="7" class="text-center">No names entered.</td></tr>';
      return;
    }

    userBatch.forEach((user, index) => {
      let statusBadge;
      let emailCellContent;
      let passwordCellContent;
      let isEmailEditable = !user.isAvailable || user.isDuplicate; // Editable if not available or duplicate
      let isPasswordEditable = user.status === "failed"; // Editable if status is failed

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

      if (isEmailEditable) {
        emailCellContent = `<span contenteditable="true" class="editable-email" data-email-index="${index}">${user.email}</span>`;
      } else {
        emailCellContent = user.email;
      }

      if (isPasswordEditable) {
        passwordCellContent = `<span contenteditable="true" class="editable-password" data-password-index="${index}">${user.password}</span>`;
      } else {
        passwordCellContent = user.password;
      }

      const row = `
                <tr>
                    <td>${index + 1}</td>
                    <td>${user.nikNip}</td>
                    <td>${user.name.toUpperCase()}</td>
                    <td>${user.unitKerja}</td>
                    <td>${emailCellContent}</td>
                    <td>${passwordCellContent}</td>
                    <td class="text-center">${statusBadge}</td>
                </tr>
            `;
      resultsTableBody.insertAdjacentHTML("beforeend", row);
    });

    // Add event listeners to editable email cells after rendering
    addEditableEmailListeners();
    // Add event listeners to editable password cells after rendering
    addEditablePasswordListeners();
  }

  function addEditableEmailListeners() {
    document.querySelectorAll(".editable-email").forEach((cell) => {
      cell.addEventListener("blur", handleEmailEdit);
      cell.addEventListener("keydown", function (e) {
        if (e.key === "Enter") {
          e.preventDefault(); // Prevent new line
          cell.blur(); // Trigger blur event
        }
      });
    });
  }

  function addEditablePasswordListeners() {
    document.querySelectorAll(".editable-password").forEach((cell) => {
      cell.addEventListener("blur", handlePasswordEdit);
      cell.addEventListener("keydown", function (e) {
        if (e.key === "Enter") {
          e.preventDefault(); // Prevent new line
          cell.blur(); // Trigger blur event
        }
      });
    });
  }

  async function handleEmailEdit(event) {
    const editedCell = event.target;
    const index = parseInt(editedCell.dataset.emailIndex);
    const oldEmail = userBatch[index].email;
    const newEmail = editedCell.textContent.trim();

    if (newEmail === oldEmail) {
      return; // No change, do nothing
    }

    if (!isValidEmail(newEmail)) {
      alert("Please enter a valid email address.");
      editedCell.textContent = oldEmail; // Revert to old email
      return;
    }

    // Update userBatch and re-check availability
    userBatch[index].email = newEmail;
    userBatch[index].isDuplicate = false; // Reset duplicate status
    userBatch[index].isAvailable = false; // Reset availability status

    // Temporarily update status badge to indicate re-checking
    const statusCell = editedCell.closest("tr").cells[6]; // Assuming status is the 7th column (index 6)
    statusCell.innerHTML = '<span class="badge bg-info">Re-checking...</span>';

    const result = await checkEmailAvailability(newEmail);
    userBatch[index].isAvailable = result.available;

    // Re-render the specific row or the entire table to reflect new status
    // For simplicity, re-rendering the entire table for now.
    renderResults(userBatch);

    // Re-evaluate submit button state
    updateSubmitButtonState();
  }

  function handlePasswordEdit(event) {
    const editedCell = event.target;
    const index = parseInt(editedCell.dataset.passwordIndex);
    const newPassword = editedCell.textContent.trim();

    // Update the password in the userBatch array
    userBatch[index].password = newPassword;
    // Change the status back to 'pending' to mark it as ready for re-submission
    userBatch[index].status = "pending";

    // Re-render the table to update the status badge visually
    renderResults(userBatch);

    // Re-evaluate the submit button state
    updateSubmitButtonState();
  }

  function isValidEmail(email) {
    // Basic email validation regex
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
  }

  function updateSubmitButtonState() {
    // Identify emails that are ready for a new submission attempt.
    // These are emails that are available, not duplicates, and have not been successfully created yet.
    validUserBatch = userBatch.filter(
      (user) =>
        !user.isDuplicate && user.isAvailable && user.status !== "created"
    );

    // Check if there are any "problematic" emails that are not yet created.
    // Problematic emails are those that are either not available or are duplicates.
    const hasProblematicPendingEmails = userBatch.some(
      (user) =>
        user.status !== "created" && (!user.isAvailable || user.isDuplicate)
    );

    // The submit button should be enabled only if:
    // 1. There is at least one valid email to submit (validUserBatch is not empty).
    // 2. There are no problematic emails that are still pending (hasProblematicPendingEmails is false).
    submitBtn.disabled =
      validUserBatch.length === 0 || hasProblematicPendingEmails;
  }
});
