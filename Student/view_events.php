<?php
session_start();
include "db.php";
if (!isset($_SESSION['role'])) {
    header("Location: index.php");
    exit();
}
$result = mysqli_query($conn, "SELECT * FROM event ORDER BY Event_id");
$total  = mysqli_num_rows($result);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Events – EventMS</title>
    <link rel="stylesheet" href="PjCss.css">
    <style>
        .event-cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 14px;
            margin-top: 16px;
        }
        .event-card {
            background: var(--bg3);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 20px 18px;
            transition: all 0.25s;
            position: relative;
            overflow: hidden;
        }
        .event-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 2px;
            background: linear-gradient(90deg, var(--gold), var(--gold-dim));
            transform: scaleX(0);
            transition: transform 0.3s;
            transform-origin: left;
        }
        .event-card:hover { border-color: var(--border2); transform: translateY(-3px); box-shadow: 0 10px 28px rgba(0,0,0,0.4); }
        .event-card:hover::before { transform: scaleX(1); }

        .ev-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .ev-id {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 12px;
            letter-spacing: 1px;
            color: var(--gold);
            background: var(--gold-glow);
            border: 1px solid var(--border2);
            padding: 3px 10px;
            border-radius: 20px;
        }
        .ev-card-name {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 18px;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 8px;
            line-height: 1.2;
        }
        .ev-card-rules {
            font-size: 13px;
            color: var(--muted);
            line-height: 1.6;
            max-height: 80px;
            overflow: hidden;
            position: relative;
        }
        .ev-card-rules.expanded { max-height: none; }
        .expand-btn {
            background: none;
            border: none;
            color: var(--gold);
            font-size: 12px;
            font-family: 'Barlow Condensed', sans-serif;
            cursor: pointer;
            padding: 4px 0;
            letter-spacing: 0.5px;
            margin-top: 6px;
            display: block;
        }
        .expand-btn:hover { text-decoration: underline; }

        .view-toggle {
            display: flex;
            gap: 6px;
            background: var(--bg3);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 4px;
        }
        .view-btn {
            padding: 7px 14px;
            border: none;
            border-radius: 5px;
            background: transparent;
            color: var(--muted);
            cursor: pointer;
            font-size: 16px;
            transition: all 0.2s;
        }
        .view-btn.active { background: var(--gold-glow); color: var(--gold); }

        .controls-row {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
            flex-wrap: wrap;
        }
        .controls-row #search-box { margin-bottom: 0; flex: 1; min-width: 180px; }

        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: var(--muted);
        }
        .empty-state .es-icon { font-size: 50px; margin-bottom: 14px; opacity: 0.4; }
        .empty-state p { font-size: 14px; }

        #no-results { display: none; }
    </style>
</head>
<body>

<div class="topbar" style="max-width:1060px;">
    <div class="logo">E</div>
    <span class="sitename">EventMS</span>
    <span class="pageinfo">Student &middot; Events</span>
</div>

<div class="card fullw">

    <span class="page-tag">Student Portal</span>
    <h2 class="title">Available <span>Events</span></h2>
    <p class="subtitle">
        <?php echo $total; ?> event<?php echo $total != 1 ? 's' : ''; ?> currently open for participation
    </p>

    <div class="controls-row">
        <input type="text" id="search-box" placeholder="&#128269;  Search events by name or ID..." oninput="filterEvents()">
        <div class="view-toggle">
            <button class="view-btn active" id="view-card" onclick="setView('card')" title="Card view">&#9638;</button>
            <button class="view-btn" id="view-table" onclick="setView('table')" title="Table view">&#9776;</button>
        </div>
    </div>

    <!-- No results message -->
    <div id="no-results" class="empty-state">
        <div class="es-icon">&#128269;</div>
        <p>No events match your search. Try a different keyword.</p>
    </div>

    <?php if ($total > 0): ?>

        <!-- Card View -->
        <div class="event-cards-grid" id="cards-view">
            <?php mysqli_data_seek($result, 0); while ($row = mysqli_fetch_assoc($result)): ?>
            <div class="event-card" data-search="<?php echo strtolower($row['Event_id'] . ' ' . $row['Event_name']); ?>">
                <div class="ev-card-header">
                    <span class="ev-id"><?php echo htmlspecialchars($row['Event_id']); ?></span>
                    <span class="open-tag">&#9679; Open</span>
                </div>
                <div class="ev-card-name"><?php echo htmlspecialchars($row['Event_name']); ?></div>
                <div class="ev-card-rules" id="rules-<?php echo htmlspecialchars($row['Event_id']); ?>">
                    <?php echo nl2br(htmlspecialchars($row['Rules'])); ?>
                </div>
                <button class="expand-btn" onclick="toggleRules('<?php echo htmlspecialchars($row['Event_id']); ?>', this)">
                    &#x25BC; Show rules
                </button>
            </div>
            <?php endwhile; ?>
        </div>

        <!-- Table View -->
        <div id="table-view" style="display:none; overflow-x:auto;">
            <table class="ev-table" id="events-table">
                <thead>
                    <tr>
                        <th>Event ID</th>
                        <th>Event Name</th>
                        <th>Rules</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php mysqli_data_seek($result, 0); while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr data-search="<?php echo strtolower($row['Event_id'] . ' ' . $row['Event_name']); ?>">
                        <td><span class="ev-id"><?php echo htmlspecialchars($row['Event_id']); ?></span></td>
                        <td><strong><?php echo htmlspecialchars($row['Event_name']); ?></strong></td>
                        <td><?php echo nl2br(htmlspecialchars($row['Rules'])); ?></td>
                        <td><span class="open-tag">&#9679; Open</span></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

    <?php else: ?>
        <div class="empty-state">
            <div class="es-icon">&#127919;</div>
            <p>No events found. Check back later.</p>
        </div>
    <?php endif; ?>

    <a href="<?php echo $_SESSION['role'] === 'admin' ? 'admin_dashboard.php' : 'student_dashboard.php'; ?>" class="back-link">&larr; Back to Dashboard</a>
</div>

<script>
var viewMode = 'card';

function setView(mode) {
    viewMode = mode;
    document.getElementById('view-card').className  = 'view-btn' + (mode === 'card'  ? ' active' : '');
    document.getElementById('view-table').className = 'view-btn' + (mode === 'table' ? ' active' : '');
    document.getElementById('cards-view').style.display = mode === 'card'  ? 'grid'  : 'none';
    document.getElementById('table-view').style.display = mode === 'table' ? 'block' : 'none';
    filterEvents();
}

function filterEvents() {
    var q = document.getElementById('search-box').value.toLowerCase().trim();
    var cards  = document.querySelectorAll('#cards-view .event-card');
    var rows   = document.querySelectorAll('#events-table tbody tr');
    var vis = 0;

    cards.forEach(function(el) {
        var match = !q || el.getAttribute('data-search').indexOf(q) >= 0;
        el.style.display = match ? '' : 'none';
        if (match) vis++;
    });
    rows.forEach(function(el) {
        var match = !q || el.getAttribute('data-search').indexOf(q) >= 0;
        el.style.display = match ? '' : 'none';
    });

    document.getElementById('no-results').style.display = (q && vis === 0) ? 'block' : 'none';
}

function toggleRules(id, btn) {
    var el = document.getElementById('rules-' + id);
    if (el.classList.contains('expanded')) {
        el.classList.remove('expanded');
        btn.innerHTML = '&#x25BC; Show rules';
    } else {
        el.classList.add('expanded');
        btn.innerHTML = '&#x25B2; Hide rules';
    }
}
</script>

</body>
</html>
