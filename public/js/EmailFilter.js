// public/js/EmailFilter.js

// This function filters the main smartlockData array to display only items that have an email.
// By default, we're checking userName for '@', but you can change to accountEmail if you prefer.
function filterByEmail() {
    if (!Array.isArray(smartlockData)) {
        console.error("[EmailFilter.js] smartlockData is not an array.");
        return;
    }

    // Option A: filter by userName containing '@'
    // const emailArray = smartlockData.filter(item => {
    //     return item.userName && item.userName.includes('@');
    // });

    // Option B: filter by accountEmail containing '@'
    const emailArray = smartlockData.filter(item => {
        return item.accountEmail && item.accountEmail.includes('@');
    });

    // Get references to the "email table" and the "no data" message
    const emailTableBody = document.getElementById('emailTableBody');
    const noEmailDataMessage = document.getElementById('noEmailDataMessage');

    // Clear the email table
    emailTableBody.innerHTML = '';

    if (emailArray.length === 0) {
        // If no results, show "no data" message
        noEmailDataMessage.style.display = 'block';
        return;
    } else {
        noEmailDataMessage.style.display = 'none';
    }

    // Populate the table with filtered results
    emailArray.forEach(item => {
        const row = document.createElement('tr');

        const deviceNameCell = document.createElement('td');
        deviceNameCell.textContent = item.deviceName || '';

        const userNameCell = document.createElement('td');
        // If you want the email to appear as 'userNameCell', you can do:
        // userNameCell.textContent = item.accountEmail || '';
        // But we'll show the userName plus we have accountEmail if needed
        userNameCell.textContent = item.accountEmail || '(No Email)';

        const creationDateCell = document.createElement('td');
        creationDateCell.classList.add('text-center');
        creationDateCell.textContent = item.creationDate || '';

        // Append all cells to the row
        row.appendChild(deviceNameCell);
        row.appendChild(userNameCell);
        row.appendChild(creationDateCell);

        emailTableBody.appendChild(row);
    });
}

// Add the click event listener for the "Show Email Accounts" button
document.getElementById('showEmailButton').addEventListener('click', filterByEmail);
