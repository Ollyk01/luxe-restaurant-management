  <tbody id="reservationTable">

            <tr>
                <td>
                    <div class="guest-info">William Ashford</div>
                    <div class="guest-contact">washford@email.com</div>
                    <div class="guest-phone">+27 78 712 3327</div>
                </td>
                <td>2024-12-16<br>19:00</td>
                <td>4</td>
                <td>Anniversary dinner champagne on arrival requested</td>
                <td>
                    <div class="deposit-amount">R280</div>
                    <div class="deposit-status">pending</div>
                </td>
                <td><span class="status-badge status-declined">Declined</span></td>
                <td><button class="action-btn view-btn">View</button></td>
            </tr>

            <tr>
                <td>
                    <div class="guest-info">Isabella Moreau</div>
                    <div class="guest-contact">izzymereau@email.com</div>
                    <div class="guest-phone">+27 64 582 8991</div>
                </td>
                <td>2024-12-16<br>20:30</td>
                <td>2</td>
                <td>Vegetarian menu required for both guests</td>
                <td>
                    <div class="deposit-amount">R150</div>
                    <div class="deposit-status">paid</div>
                </td>
                <td><span class="status-badge status-confirmed">Confirmed</span></td>
                <td><button class="action-btn view-btn">View</button></td>
            </tr>

            <tr>
                <td>
                    <div class="guest-info">The Chen Party</div>
                    <div class="guest-contact">chenfamilyemail.com</div>
                    <div class="guest-phone">+27 506 3462</div>
                </td>
                <td>2024-12-17<br>19:30</td>
                <td>8</td>
                <td>Private dining room, multiple dietary restrictions</td>
                <td>
                    <div class="deposit-amount">R250</div>
                    <div class="deposit-status">pending</div>
                </td>
                <td><span class="status-badge status-pending">Pending</span></td>
                <td>
                    <button class="action-btn success accept-btn">Accept</button>
                    <button class="action-btn danger decline-btn">Decline</button>
                </td>
            </tr>

            </tbody>
        </table>
    </div>
</div>

<script>
    /* ALERT CLOSE */
    document.getElementById("closeAlert").addEventListener("click", function () {
        document.getElementById("alertBanner").style.display = "none";
    });

    /* FILTER */
    const tabs = document.querySelectorAll(".filter-tab");
    const rows = document.querySelectorAll("#reservationTable tr");

    tabs.forEach(tab => {
        tab.addEventListener("click", () => {

            tabs.forEach(t => t.classList.remove("active"));
            tab.classList.add("active");

            const filter = tab.getAttribute("data-filter");

            rows.forEach(row => {
                const statusEl = row.querySelector(".status-badge");
                if (!statusEl) return;

                const status = statusEl.textContent.toLowerCase();

                if (filter === "all") {
                    row.style.display = "";
                } else {
                    row.style.display = status.includes(filter) ? "" : "none";
                }
            });
        });
    });

    /* ACCEPT */
    document.querySelectorAll(".accept-btn").forEach(btn => {
        btn.addEventListener("click", () => {
            const row = btn.closest("tr");
            const badge = row.querySelector(".status-badge");
            badge.textContent = "Confirmed";
            badge.className = "status-badge status-confirmed";
        });
    });

    /* DECLINE */
    document.querySelectorAll(".decline-btn").forEach(btn => {
        btn.addEventListener("click", () => {
            const row = btn.closest("tr");
            const badge = row.querySelector(".status-badge");
            badge.textContent = "Declined";
            badge.className = "status-badge status-declined";
        });
    });

    /* VIEW */
    document.querySelectorAll(".view-btn").forEach(btn => {
        btn.addEventListener("click", () => {
            const row = btn.closest("tr");
            const name = row.querySelector(".guest-info").textContent;
            alert("Viewing reservation for: " + name);
        });
    });

   
    document.getElementById("logoutBtn").addEventListener("click", () => {
        window.location.href = "adlogin.html";
    });
</script>
