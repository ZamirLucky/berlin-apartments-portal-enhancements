function handleSendCodeButton(button) {
    const smartlockId   = button.getAttribute("data-id");
    const accountUserId = button.getAttribute("data-account-user-id");
    const userName      = button.closest("tr").children[1].innerText;

    console.log("[AuthorizationCode.js] 'Send Code' clicked =>", {
        smartlockId,
        accountUserId,
        userName
    });

    button.disabled = true;
    const originalText = button.textContent;
    button.textContent = "Processing...";

    if (!accountUserId || accountUserId === "null") {
        alert("No valid account user ID. We cannot send the code for this user.");
        button.disabled = false;
        button.textContent = originalText;
        return;
    }

    const payload = { smartlockId, accountUserId, userName };

    fetch("../../app/controllers/AuthorizationCodeController.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload)
    })
    .then(response => {
        console.log("[AuthorizationCode.js] Fetch response status:", response.status);
        if (!response.ok) {
            throw new Error("HTTP error, status = " + response.status);
        }
        return response.json();
    })
    .then(data => {
        console.log("[AuthorizationCode.js] Server response JSON:", data);

        button.disabled = false;
        button.textContent = originalText;

        if (!data.error) {
            alert("Authorization code sent successfully!");
        } else {
            alert("Error sending code: " + data.details);
        }
    })
    .catch(error => {
        console.error("[AuthorizationCode.js] Fetch error:", error);
        button.disabled = false;
        button.textContent = originalText;
        alert("Error: " + error.message);
    });
}

function handleExtendDateButton(button) {
    const smartlockId     = button.getAttribute("data-id");
    const userName        = button.closest("tr").children[1].innerText;
    let allowedFromDate   = button.closest("tr").children[3].innerText;
    let allowedUntilDate  = button.closest("tr").children[4].innerText;

    // If either cell is "N/A", turn it into null or an empty string
    if (allowedFromDate === "N/A") {
        allowedFromDate = null;
    }
    if (allowedUntilDate === "N/A") {
        allowedUntilDate = null;
    }

    console.log("[AuthorizationCode.js] 'Extend Date' clicked =>", {
        smartlockId,
        userName,
        allowedFromDate,
        allowedUntilDate
    });

    // IMPORTANT: We send `name` instead of `userName`, 
    // because PHP expects `$data['name']`.
    const payload = {
        smartlockId,
        name: userName,
        allowedFromDate,
        allowedUntilDate,
        addDays: 3
    };

    fetch("../../app/controllers/ExtendDateController.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload)
    })
    .then(response => {
        console.log("[AuthorizationCode.js] ExtendDate fetch status:", response.status);
        if (!response.ok) {
            throw new Error("HTTP error, status = " + response.status);
        }
        return response.json();
    })
    .then(data => {
        console.log("[AuthorizationCode.js] ExtendDate response JSON:", data);

        if (!data.error) {
            alert("Date extended successfully!");

            // Optionally update the row so the table displays the new dates:
            if (data.responseNewFromDate) {
                button.closest("tr").children[3].innerText = data.responseNewFromDate;
            }
            if (data.responseNewUntilDate) {
                button.closest("tr").children[4].innerText = data.responseNewUntilDate;
            }
        } else {
            alert("Error extending date: " + data.details);
        }
    })
    .catch(error => {
        console.error("[AuthorizationCode.js] Extend date fetch error:", error);
        alert("Error: " + error.message);
    });
}
