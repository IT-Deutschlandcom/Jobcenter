<?php

function rank_catalog()
{
    $tac = [
        'Prospect', 'Cadet', 'Junior Agent', 'Agent', 'Senior Agent',
        'Field Agent', 'Senior Field Agent', 'Tactical Agent', 'Senior Tactical Agent',
        'Special Agent', 'Senior Special Agent', 'Response Agent', 'Senior Response Agent',
        'Operations Trainee', 'Junior Operations Agent', 'Operations Agent',
        'Advanced Operations Agent', 'Lead Operations Agent', 'Senior Operations Agents'
    ];
    $intel = [
        'Investigator', 'Senior Investigator', 'Intelligence Agent', 'Senior Intelligence Agent',
        'Intelligence Analyst', 'Senior Intelligence Analyst', 'Field Investigator',
        'Senior Field Investigator', 'Special Investigator', 'Senior Special Investigator',
        'Intelligence Specialist', 'Senior Intelligence Specialist', 'Operations Analyst',
        'Senior Operations Analyst', 'Intelligence Coordinator', 'Senior Intelligence Coordinator',
        'Intelligence Officer', 'Senior Intelligence Officer', 'Intelligence Supervisor',
        'Senior Intelligence Supervisor', 'Intelligence Manager', 'Senior Intelligence Manager',
        'Intelligence Commander', 'Senior Intelligence Commander', 'Intelligence Advisor',
        'Senior Intelligence Advisor', 'Europol Specialist', 'Europol Expert',
        'Europol Master', 'Europol Elite'
    ];
    $ranks = [];
    foreach ($tac as $index => $name) {
        $ranks[$index + 1] = ['name' => $name, 'division' => 'TAC', 'steps' => 0];
    }
    foreach ($intel as $index => $name) {
        $ranks[$index + 20] = ['name' => $name, 'division' => 'Intel', 'steps' => 10];
    }
    foreach (['Squad Leader', 'Senior Squad Leader', 'Commander', 'Chief Commander',
              'Senior Chief Commander', 'Director'] as $index => $name) {
        $ranks[$index + 50] = ['name' => $name, 'division' => 'Leadership', 'steps' => 0];
    }
    return $ranks;
}

function rank_label($user)
{
    $rank = rank_catalog()[(int) $user['rank_id']] ?? null;
    if (!$rank) {
        return 'Unbekannter Rang';
    }
    return $rank['name'] . ($rank['division'] === 'Intel' ? ' ' . (int) $user['rank_step'] . '/10' : '');
}

function can_manage($user)
{
    return $user && (int) $user['rank_id'] >= 50;
}

function can_view_payroll($user)
{
    return $user && (int) $user['rank_id'] >= 55;
}

function next_rank($user)
{
    $id = (int) $user['rank_id'];
    $step = (int) $user['rank_step'];
    // Führungsrechte werden ausschließlich durch die Administration vergeben.
    if ($id >= 50) {
        return null;
    }
    if ($id >= 20 && $step < 10) {
        return ['rank_id' => $id, 'rank_step' => $step + 1];
    }
    if ($id >= 49) {
        return null;
    }
    return ['rank_id' => $id + 1, 'rank_step' => 0];
}
