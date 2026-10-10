<?php
require_once __DIR__ . '/layout.php';
$user = require_login();
page_header('Führungsebene', $user);
$leaders = service_query('SELECT username, rank_id, rank_step FROM users WHERE rank_id >= 50 ORDER BY rank_id DESC, username')->fetchAll();
?>
<section class="card">
    <h2>Aktuelle Führung</h2>
    <div class="table-scroll"><table><thead><tr><th>Mitarbeiter</th><th>Rang</th></tr></thead><tbody>
    <?php foreach ($leaders as $leader): ?>
        <tr><td><?= h($leader['username']) ?></td><td><?= h(rank_label($leader)) ?></td></tr>
    <?php endforeach; ?>
    <?php if (!$leaders): ?><tr><td colspan="2">Noch keine Führungskraft ernannt.</td></tr><?php endif; ?>
    </tbody></table></div>
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
