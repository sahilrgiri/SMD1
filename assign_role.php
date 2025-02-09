

<form action="assign_role_process.php" method="post">
    <label for="operator">Select Operator:</label>
    <select name="operator" required>
        <?php
        $sql = "SELECT id, username FROM users WHERE role = 'operator'";
        $result = $conn->query($sql);
        while ($row = $result->fetch_assoc()) {
            echo "<option value='" . $row['id'] . "'>" . $row['username'] . "</option>";
        }
        ?>
    </select>
    <br>

    <label for="machine">Assign Machine(s):</label>
    <select name="machine[]" multiple required>
        <?php
        $sql = "SELECT machine_id, machine_name FROM machines";
        $result = $conn->query($sql);
        while ($row = $result->fetch_assoc()) {
            echo "<option value='" . $row['machine_id'] . "'>" . $row['machine_name'] . "</option>";
        }
        ?>
    </select>
    <br>

    <label for="job">Assign Job(s):</label>
    <select name="job[]" multiple required>
        <?php
        $sql = "SELECT job_id, job_description FROM jobs";
        $result = $conn->query($sql);
        while ($row = $result->fetch_assoc()) {
            echo "<option value='" . $row['job_id'] . "'>" . $row['job_description'] . "</option>";
        }
        ?>
    </select>
    <br>

    <button type="submit">Assign</button>
</form>
