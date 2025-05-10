<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Competition Management System</title>
    <link rel="stylesheet" href="./judge_dash.css">
    <style>
        a.button {
            display: inline-block;
            background-color: #007BFF;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
            border-radius: 4px;
            text-decoration: none;
            text-align: center;
        }

        a.button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <?php include 'database.php'; ?>

    <header>
        <div class="navbar">
            <span class="logo">Competition Management System</span>
            <nav>
                <a href="#">Home</a>
                <a href="#">Competitions</a>
                <a href="#">Contact</a>
                <a href="#">Updates</a>
                <a href="#">About Us</a>
                <a href="#">Help</a>
                <button id="logout-button">Logout</button>
            </nav>
        </div>
    </header>

    <main>
        <section>
            <h1>Upcoming Competitions</h1>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Competition Title</th>
                            <th>Details</th>
                            <th>Starting Date</th>
                            <th>Ending Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="upcoming-competitions">
                        <?php
                        $query = "SELECT * FROM Competition ORDER BY date ASC";
                        $result = mysqli_query($conn, $query);
                        $current_date = date('Y-m-d');
                        $current_time = date('H:i:s');

                        if (mysqli_num_rows($result) > 0) {
                            $index = 1;
                            while ($row = mysqli_fetch_assoc($result)) {
                                $start_date = $row['date'];
                                $start_time = $row['starting_time'];
                                $end_date = $row['date'];
                                $end_time = $row['ending_time'];

                                // Determine the status
                                if ($start_date > $current_date || ($start_date == $current_date && $start_time > $current_time)) {
                                    $status = "Upcoming";
                                } elseif ($end_date < $current_date || ($end_date == $current_date && $end_time < $current_time)) {
                                    $status = "Ended";
                                } else {
                                    $status = "Ongoing";
                                }

                                echo "<tr>";
                                echo "<td>" . $index . "</td>";
                                echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                                echo "<td><a href=\"judge_details.php?compId=" . $row['event_id'] . "\" class=\"button\">Details</a></td>";
                                echo "<td>" . htmlspecialchars($start_date . " " . $start_time) . "</td>";
                                echo "<td>" . htmlspecialchars($end_date . " " . $end_time) . "</td>";
                                echo "<td>" . htmlspecialchars($status) . "</td>";
                                echo "</tr>";
                                $index++;
                            }
                        } else {
                            echo "<tr><td colspan='6'>No upcoming competitions found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section>
            <h1>Previous Competitions</h1>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Competition Name</th>
                            <th>Competition ID</th>
                        </tr>
                    </thead>
                    <tbody id="previous-competitions">
                        <?php
                        $query = "SELECT * FROM Competition WHERE date < CURDATE() ORDER BY date DESC";
                        $result = mysqli_query($conn, $query);

                        if (mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['date']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['event_id']) . "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='4'>No previous competitions found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section id="previous-results" class="hidden">
            <h1>Previous Results</h1>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>S.no</th>
                            <th>Date</th>
                            <th>Name</th>
                            <th>Position</th>
                            <th>Prized (Yes/No)</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody id="previous-results-body">
                        <!-- Dynamically generated result rows -->
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.showResults = function(eventId) {
                const xhr = new XMLHttpRequest();
                xhr.open('POST', 'get_competition_results.php', true);
                xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
                xhr.onload = function() {
                    if (this.status == 200) {
                        const results = JSON.parse(this.responseText);
                        const resultsBody = document.getElementById('previous-results-body');
                        resultsBody.innerHTML = '';

                        results.forEach((result, index) => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td>${index + 1}</td>
                                <td>${result.date}</td>
                                <td>${result.name}</td>
                                <td>${result.position}</td>
                                <td>${result.prized ? 'Yes' : 'No'}</td>
                                <td>${result.notes}</td>
                            `;
                            resultsBody.appendChild(row);
                        });

                        document.getElementById('previous-results').classList.remove('hidden');
                    }
                };
                xhr.send('eventId=' + eventId);
            };

            document.getElementById('logout-button').addEventListener('click', function() {
                if (confirm('Are you sure you want to logout?')) {
                    window.location.href = 'index.php';
                }
            });
        });
    </script>
</body>
</html>
