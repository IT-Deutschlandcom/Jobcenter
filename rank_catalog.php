<?php

function rank_catalog()
{
    $ranks = [];
    $groups = [
        1 => ['TAC', 0, [
            'Prospect', 'Cadet', 'Trainee', 'Junior Cadet', 'Operative', 'Support', 'Assistant',
            'Clerk', 'Officer', 'Junior Officer', 'Field Officer', 'Patrol Unit', 'Tactical Unit',
            'Europol Agent', 'Junior Agent', 'Field Agent', 'Tactical Agent', 'Operations Agent',
            'Senior Operations Agent',
        ]],
        20 => ['Intel', 10, [
            'Investigator', 'Junior Investigator', 'Senior Investigator', 'Intelligence Agent',
            'Intelligence Specialist', 'Operations Specialist', 'Senior Agent', 'Lead Agent',
            'Elite Agent', 'Shadow Agent', 'Intelligence Expert', 'Senior Operative', 'Elite Operative',
            'Command Operative', 'Tactical Expert',
        ]],
        35 => ['INV', 20, [
            'Operations Expert', 'Elite Specialist', 'Vanguard', 'Sentinel', 'Enforcer',
            'Elite Enforcer', 'Veteran Agent', 'Elite Veteran', 'Master Agent', 'Apex Agent',
            'Senior Elite', 'High Elite', 'Supreme Agent', 'Elite Commander', 'Europol Elite',
        ]],
        50 => ['Unit Leadership', 0, [
            'Squad Leader', 'Senior Squad Leader', 'Unit Commander', 'Senior Unit Commander',
            'Operations Commander', 'Tactical Commander', 'Intelligence Commander',
            'Investigation Commander', 'Chief Commander', 'Senior Chief Commander',
        ]],
        60 => ['Division Leadership', 0, [
            'Division Chief', 'Senior Division Chief', 'Operations Director', 'Tactical Director',
            'Intelligence Director', 'Deputy Director', 'Director', 'Senior Director',
            'Executive Director',
        ]],
        69 => ['High Command', 0, [
            'Vice Commissioner', 'Commissioner', 'High Commissioner', 'Europol Chief',
            'Director General', 'Co Founder', 'Europol Supreme / Founder',
        ]],
    ];
    foreach ($groups as $firstRank => [$division, $steps, $names]) {
        foreach ($names as $offset => $name) {
            $ranks[$firstRank + $offset] = [
                'name' => $name,
                'division' => $division,
                'steps' => $steps,
            ];
        }
    }
    return $ranks;
}

function rank_label($user)
{
    $rank = rank_catalog()[(int) $user['rank_id']] ?? null;
    if (!$rank) {
        return 'Unbekannter Rang';
    }
    return $rank['name'] . ($rank['steps'] ? ' ' . (int) $user['rank_step'] . '/' . $rank['steps'] : '');
}

function can_manage($user)
{
    return $user && (int) $user['rank_id'] >= 50 && (int) $user['rank_id'] <= 75;
}

function can_view_payroll($user)
{
    return $user && (int) $user['rank_id'] >= 65 && (int) $user['rank_id'] <= 75;
}

function can_manage_payroll_periods($user)
{
    return can_view_payroll($user);
}

function next_rank($user)
{
    $id = (int) $user['rank_id'];
    $step = (int) $user['rank_step'];
    if ($id >= 50) {
        return null;
    }
    $ranks = rank_catalog();
    if (!isset($ranks[$id])) {
        return null;
    }
    if ($ranks[$id]['steps'] > 0 && $step < $ranks[$id]['steps']) {
        return ['rank_id' => $id, 'rank_step' => $step + 1];
    }
    if ($id >= 49) {
        return null;
    }
    return ['rank_id' => $id + 1, 'rank_step' => 0];
}
