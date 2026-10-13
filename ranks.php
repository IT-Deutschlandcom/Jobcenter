<?php
require_once __DIR__ . '/layout.php';
$user = require_login();
page_header('Führungsebene', $user);
$ranks = rank_catalog();
$membersByRank = array_fill_keys(array_keys($ranks), []);
$members = service_query('SELECT username, rank_id, rank_step FROM users ORDER BY rank_id, username')->fetchAll();
foreach ($members as $member) {
    $rankId = (int) $member['rank_id'];
    if (isset($membersByRank[$rankId])) {
        $membersByRank[$rankId][] = $member;
    }
}
$divisions = [
    'Intel' => ['label' => 'Intelligence Division', 'image' => 'https://github.com/user-attachments/assets/b3ec7c79-875f-44cb-9d30-f90f37b3abaa'],
    'INV' => ['label' => 'Investigation Division · INV-01 Criminal Investigation Unit', 'image' => 'https://github.com/user-attachments/assets/1fc15267-4a99-425d-9c37-dfa9dc3099d0'],
    'TAC' => ['label' => 'Tactical Division · TAC-01 Patrol & Response Unit', 'image' => 'https://github.com/user-attachments/assets/1fc15267-4a99-425d-9c37-dfa9dc3099d0'],
    'Unit Leadership' => ['label' => 'Unit Leadership · TAC-CMD / INT-CMD / INV-CMD', 'image' => 'https://github.com/user-attachments/assets/3dc8cd4a-c14e-4445-b9f6-6ca9d3c46fb3'],
    'Division Leadership' => ['label' => 'Division Leadership · CMD-01 Command Division', 'image' => 'https://github.com/user-attachments/assets/3dc8cd4a-c14e-4445-b9f6-6ca9d3c46fb3'],
    'High Command' => ['label' => 'High Command · CMD-02 High Command', 'image' => 'https://github.com/user-attachments/assets/3dc8cd4a-c14e-4445-b9f6-6ca9d3c46fb3'],
];
?>
<section class="card">
    <h2>Aktuelle Führung und Mitarbeiter</h2>
    <div class="division-list">
        <?php foreach ($divisions as $division => $details): ?>
            <section class="division-card" aria-labelledby="division-<?= h(strtolower($division)) ?>">
                <h3 class="division-heading" id="division-<?= h(strtolower($division)) ?>">
                    <img src="<?= h($details['image']) ?>" alt="" width="40" height="40">
                    <?= h($details['label']) ?>
                </h3>
                <?php foreach ($ranks as $rankId => $rank): ?>
                    <?php if ($rank['division'] !== $division) { continue; } ?>
                    <section class="rank-group" aria-label="<?= h($rank['name']) ?>">
                        <h4><?= (int) $rankId ?>. <?= h($rank['name']) ?><?= $rank['steps'] ? ' · 0/' . (int) $rank['steps'] . '–' . (int) $rank['steps'] . '/' . (int) $rank['steps'] : '' ?></h4>
                        <?php if ($membersByRank[$rankId]): ?>
                            <ul class="member-list">
                                <?php foreach ($membersByRank[$rankId] as $member): ?>
                                    <li class="member-card">
                                        <img
                                            class="member-avatar"
                                            src="https://www.habbo.de/habbo-imaging/avatarimage?headonly=1&amp;head_direction=2&amp;gesture=std&amp;size=l&amp;user=<?= rawurlencode($member['username']) ?>"
                                            alt=""
                                            width="48"
                                            height="48"
                                            loading="lazy">
                                        <span class="member-details">
                                            <strong><?= h($member['username']) ?></strong>
                                            <span><?= h(rank_label($member)) ?></span>
                                        </span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p class="empty-rank">Noch keine Mitarbeiter in diesem Rang.</p>
                        <?php endif; ?>
                    </section>
                <?php endforeach; ?>
            </section>
        <?php endforeach; ?>
    </div>
</section>
<section class="card">
    <h2>Historisches Team</h2>
    <p>Die folgende Liste stammt aus dem bisherigen Jobcenter. Maßgeblich für BF-Rechte ist ausschließlich der aktuelle Kontorang.</p>
			    <div id="cc-matrix-3201134325"><div id="cc-m-11656359825" class="n j-text"><p style="text-align: center;" data-mce-style="text-align: center;">
    <span style="font-size: 24px;" data-mce-style="font-size: 24px;">[-Ministerium-]</span>
</p>

<p style="text-align: center;" data-mce-style="text-align: center;">
     <br/>
</p>

<p style="text-align: center;" data-mce-style="text-align: center;">
    RockerFlippy : Minister of EURO
</p>

<p style="text-align: center;" data-mce-style="text-align: center;">
    Painetik : Minister of Management
</p>

<p style="text-align: center;" data-mce-style="text-align: center;">
    Shahrukh008 : Minister of Discipline
</p>

<p style="text-align: center;" data-mce-style="text-align: center;">
    Lordblekkinator : Minister of General
</p>

<p style="text-align: center;" data-mce-style="text-align: center;">
     <br/>
</p>

<p style="text-align: center;" data-mce-style="text-align: center;">
    <span style="font-size: 24px;" data-mce-style="font-size: 24px;"> [-Cabinet-]</span>
</p>

<p style="text-align: center;" data-mce-style="text-align: center;">
    Grillengriller : Cabinet of Structure<br/>
</p>

<p style="text-align: center;" data-mce-style="text-align: center;">
     trainstyle : Cabinet of Work<br/>
</p>

<p style="text-align: center;" data-mce-style="text-align: center;">
    leonard2003 : Cabinet of Technical Systems
</p>

<p style="text-align: center;" data-mce-style="text-align: center;">
    Pandarial : Cabinet of Fairness
</p>

<p style="text-align: center;" data-mce-style="text-align: center;">
     <br/>
</p>

<p style="text-align: center;" data-mce-style="text-align: center;">
    <span style="font-size: 24px;" data-mce-style="font-size: 24px;">[-Generals-]</span>
</p>

<p style="text-align: center;" data-mce-style="text-align: center;">
    add3mee : Head General
</p>

<p style="text-align: center;" data-mce-style="text-align: center;">
     <br/>
</p>

<p style="text-align: center;" data-mce-style="text-align: center;">
    Kidink22 : Leading General
</p>

<p style="text-align: center;" data-mce-style="text-align: center;">
    overHero : Leading General
</p>

<p style="text-align: center;" data-mce-style="text-align: center;">
    Banana?? : Leading General
</p>

<p style="text-align: center;" data-mce-style="text-align: center;">
    Bina.- : Leading General
</p>

<p style="text-align: center;" data-mce-style="text-align: center;">
     ALBoz55 : Leading General
</p>

<p style="text-align: center;" data-mce-style="text-align: center;">
     
</p>

<p style="text-align: center;" data-mce-style="text-align: center;">
    akim1505 : Vice General
</p>

<p style="text-align: center;" data-mce-style="text-align: center;">
    ClaudiaNguyen : Vice General
</p>

<p style="text-align: center;" data-mce-style="text-align: center;">
    kingfatjon : Vice General
</p>

<p style="text-align: center;" data-mce-style="text-align: center;">
    !p=linda=q! : Vice General
</p>

<p style="text-align: center;" data-mce-style="text-align: center;">
    sneychica : Vice General
</p>

<p style="text-align: center;" data-mce-style="text-align: center;">
     <br/>
</p>

<p style="text-align: center;" data-mce-style="text-align: center;">
    lexono : Rear General
</p>

<p style="text-align: center;" data-mce-style="text-align: center;">
    EvNewGirl : Rear General
</p>

<p style="text-align: center;" data-mce-style="text-align: center;">
    Das:L. : Rear General
</p>

<p style="text-align: center;" data-mce-style="text-align: center;">
    MissOptora : Rear General
</p>

<p style="text-align: center;" data-mce-style="text-align: center;">
    annemonebarth : Rear General
</p>

<p style="text-align: center;" data-mce-style="text-align: center;">
      Hanna2006 : Rear General
</p>

<p style="text-align: center;" data-mce-style="text-align: center;">
    Chewbacca_PVP : Rear General
</p>

<p style="text-align: center;" data-mce-style="text-align: center;">
    Weekdays : Rear General
</p>

<p style="text-align: center;" data-mce-style="text-align: center;">
    !!Gangsterboss!! : Rear General
</p>

<p style="text-align: center;" data-mce-style="text-align: center;">
     <br/>
</p>

<p style="text-align: center;" data-mce-style="text-align: center;">
    KingPhilDeluxe : Base General
</p>

<p style="text-align: center;" data-mce-style="text-align: center;">
    -The.Fear- : Base General
</p>

<p style="text-align: center;" data-mce-style="text-align: center;">
     <br/>
</p>

<p style="text-align: center;" data-mce-style="text-align: center;">
    [-Der Rang "Base General" ist f&uuml;r 500 Taler k&auml;uflich erwerblich!-]
</p>

<p style="text-align: center;" data-mce-style="text-align: center;">
     <br/>
</p>

<p style="text-align: center;" data-mce-style="text-align: center;">
    [-Der Rang "Trainer" ist f&uuml;r 200 Taler k&auml;uflich erwerblich!-]
</p>

<p style="text-align: center;" data-mce-style="text-align: center;">
     <br/>
</p>

<p style="text-align: center;" data-mce-style="text-align: center;">
     <br/>
</p>

<p style="text-align: center;" data-mce-style="text-align: center;">
    Es wurden neue Trainerr&auml;nge eingef&uuml;hrt:
</p>

<p style="text-align: center;" data-mce-style="text-align: center;">
     <br/>
</p>

<p style="text-align: center;" data-mce-style="text-align: center;">
    1. Trainer of Time
</p>

<p style="text-align: center;" data-mce-style="text-align: center;">
    2. Trainer of Science
</p>

<p style="text-align: center;" data-mce-style="text-align: center;">
    3. Trainer of Executive
</p>

<p style="text-align: center;" data-mce-style="text-align: center;">
    4. Trainer of Operation
</p>

<p style="text-align: center;" data-mce-style="text-align: center;">
    5. Trainer of Countdown
</p>

<p style="text-align: center;" data-mce-style="text-align: center;">
    6. Trainer of Device
</p>

<p style="text-align: center;" data-mce-style="text-align: center;">
    7. Trainer of Council
</p>

<p style="text-align: center;" data-mce-style="text-align: center;">
    8. Trainer of Master
</p>

<p style="text-align: center;" data-mce-style="text-align: center;">
    9. Head Trainer
</p>

<p style="text-align: center;" data-mce-style="text-align: center;">
     <br/>
</p>

<p style="text-align: center;" data-mce-style="text-align: center;">
     <br/>
</p>

<p style="text-align: center;" data-mce-style="text-align: center;">
    Der Colonel of Trainer ist add3mee, bei Fragen, wendet Euch bitte an ihn.
</p>

<p style="text-align: center;" data-mce-style="text-align: center;">
     <br/>
</p>

<p style="text-align: center;" data-mce-style="text-align: center;">
     <br/>
</p></div></div>
                </section>
                              
			 <br>
               
		
		
	
		        	
	
	
<?php page_footer(); ?>
