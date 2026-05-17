<?php
/**
 * views/recruiter/search_seekers.php
 * Headhunting interface to search for candidates.
 */

require_once '../../app/core/Session.php';
require_once '../../app/controllers/RecruiterController.php';

Session::init();
Session::checkRole('recruiter');

$controller = new RecruiterController();
$seekers = [];

if (isset($_GET['q']) || isset($_GET['skills'])) {
    $seekers = $controller->searchSeekers();
}

include '../partials/header.php';
?>

<div class="container" style="margin-top: 30px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h1>Headhunt Candidates</h1>
        <a href="dashboard.php" class="btn-primary" style="background: #35424a;">Back to Dashboard</a>
    </div>

    <?php if (isset($_GET['msg'])): ?>
        <div style="padding: 15px; background: #d4edda; color: #155724; border-radius: 4px; margin-bottom: 20px;">
            <?= htmlspecialchars($_GET['msg']) ?>
        </div>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
        <div style="padding: 15px; background: #f8d7da; color: #721c24; border-radius: 4px; margin-bottom: 20px;">
            <?= htmlspecialchars($_GET['error']) ?>
        </div>
    <?php endif; ?>

    <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 30px; border: 1px solid #eee;">
        <form action="search_seekers.php" method="GET" style="display: flex; gap: 15px;">
            <div style="flex: 2;">
                <label style="display: block; font-weight: bold; margin-bottom: 5px;">Keywords (Headline, Name, Summary,Location, Experience, Salary)</label>
                <input type="text" name="q" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" placeholder="e.g. Software Engineer" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
            </div>
            <div style="flex: 1;">
                <label style="display: block; font-weight: bold; margin-bottom: 5px;">Skills</label>
                <input type="text" name="skills" value="<?= htmlspecialchars($_GET['skills'] ?? '') ?>" placeholder="e.g. PHP, React" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
            </div>
            <div style="display: flex; align-items: flex-end;">
                <button type="submit" class="btn-primary" style="padding: 10px 20px; height: 40px; cursor: pointer;">Search</button>
            </div>
        </form>
    </div>

    <div>
        <?php if ((isset($_GET['q']) || isset($_GET['skills'])) && empty($seekers)): ?>
            <p style="text-align: center; color: #888;">No candidates found matching your criteria.</p>
        <?php elseif (!empty($seekers)): ?>
            <h3>Search Results (<?= count($seekers) ?>)</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
                <?php foreach ($seekers as $seeker): ?>
                    <div style="border: 1px solid #ddd; border-radius: 8px; padding: 20px; background: white; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                        <h4 style="margin: 0 0 5px 0; color: #35424a; font-size: 18px;"><?= htmlspecialchars($seeker['name']) ?></h4>
                        <p style="margin: 0 0 10px 0; color: #e8491d; font-weight: bold; font-size: 14px;">
                            <?= htmlspecialchars($seeker['headline'] ?? 'No headline') ?>
                        </p>
                        
                        <p style="margin: 5px 0; font-size: 13px; color: #555;">
                            <strong>Experience:</strong> <?= $seeker['years_experience'] ?> years
                        </p>
                        <p style="margin: 5px 0 15px 0; font-size: 13px; color: #555;">
                            <strong>Skills:</strong> <?= htmlspecialchars($seeker['skills'] ?? 'None specified') ?>
                        </p>
                        
                        <div style="border-top: 1px solid #eee; padding-top: 15px; text-align: center;">
                            <a href="outreach.php?seeker_id=<?= $seeker['user_id'] ?>" class="btn-primary" style="display: inline-block; width: 100%; text-align: center; box-sizing: border-box; background: #28a745;">Headhunt (Send Outreach)</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p style="text-align: center; color: #888;">Enter search criteria above to find candidates.</p>
        <?php endif; ?>
    </div>
</div>

<?php include '../partials/footer.php'; ?>
