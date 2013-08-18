<?php

function get_credit_badge($user) {
    $badges = "";

    if        ($user->total_credit > 500000000) {
        $badges .= "<img style='vertical-align:middle; height:28px;' src='./wildlife_badges/credit_500000000_DoubleJadeEm.png' title='A double jade-emerald badge for earning 500M credit.'></img>";
    } else if ($user->total_credit > 200000000) {
        $badges .= "<img style='vertical-align:middle; height:28px;' src='./wildlife_badges/credit_200000000_DoubleSaphTurq.png' title='A double sapphire-turquoise badge for earning 200M credit.'></img>";
    } else if ($user->total_credit > 100000000) {
        $badges .= "<img style='vertical-align:middle; height:28px;' src='./wildlife_badges/credit_100000000_DoubleAmethRuby.png' title='A double amethyest-ruby badge for earning 100M credit.'></img>";
    } else if ($user->total_credit > 50000000) {
        $badges .= "<img style='vertical-align:middle; height:28px;' src='./wildlife_badges/credit_50000000_DoubleGold.png' title='A double gold badge for earning 50M credit.'></img>";
    } else if ($user->total_credit > 20000000) {
        $badges .= "<img style='vertical-align:middle; height:28px;' src='./wildlife_badges/credit_20000000_DoubleSilver.png' title='A double silver badge for earning 20M credit.'></img>";
    } else if ($user->total_credit > 10000000) {
        $badges .= "<img style='vertical-align:middle; height:28px;' src='./wildlife_badges/credit_10000000_DoubleBronze.png' title='A double bronze badge for earning 10M credit.'></img>";
    } else if ($user->total_credit > 5000000) {
        $badges .= "<img style='vertical-align:middle; height:28px;' src='./wildlife_badges/credit_5000000_JadeEm.png' title='A jade-emerald badge for earning 2M credit.'></img>";
    } else if ($user->total_credit > 2000000) {
        $badges .= "<img style='vertical-align:middle; height:28px;' src='./wildlife_badges/credit_2000000_Saphturq.png' title='A sapphire-turquoise badge for earning 2M credit.'></img>";
    } else if ($user->total_credit > 1000000) {
        $badges .= "<img style='vertical-align:middle; height:28px;' src='./wildlife_badges/credit_1000000_AmethRuby.png' title='An amethyst-ruby badge for earning 1M credit.'></img>";
    } else if ($user->total_credit > 500000) {
        $badges .= "<img style='vertical-align:middle; height:28px;' src='./wildlife_badges/credit_500000_Gold.png' title='A gold badge for earning 500k credit.'></img>";
    } else if ($user->total_credit > 100000) {
        $badges .= "<img style='vertical-align:middle; height:28px;' src='./wildlife_badges/credit_100000_Silber.png' title='A silver badge for earning 100k credit.'></img>";
    } else if ($user->total_credit > 10000) {
        $badges .= "<img style='vertical-align:middle; height:28px;' src='./wildlife_badges/credit_10000_Bronze.png' title='A bronze badge for earning 10k credit.'></img>";
    }

    return $badges;
}

function get_bossa_badge($user) {
    $badges = "";

    if        ($user->bossa_total_credit > 10000000) {
        $badges .= "<img style='height:40px;' src='./wildlife_badges/bossa_10000000_Feder3TripleBronze.png' title='A triple bronze badge for earning 10M validated seconds of watched video.'></img>";
    } else if ($user->bossa_total_credit >  9000000) {
        $badges .= "<img style='height:40px;' src='./wildlife_badges/bossa_9000000_Feder2DoubleEmerald.png' title='A double emerald badge for earning 9M validated seconds of watched video.'></img>";
    } else if ($user->bossa_total_credit >  8000000) {
        $badges .= "<img style='height:40px;' src='./wildlife_badges/bossa_8000000_Feder2DoubleJade.png' title='A double jade badge for earning 8M validated seconds of watched video.'></img>";
    } else if ($user->bossa_total_credit >  7000000) {
        $badges .= "<img style='height:40px;' src='./wildlife_badges/bossa_7000000_Feder2DoubleSaph.png' title='A double sapphire badge for earning 7M validated seconds of watched video.'></img>";
    } else if ($user->bossa_total_credit >  6000000) {
        $badges .= "<img style='height:40px;' src='./wildlife_badges/bossa_6000000_Feder2DoubleTurq.png' title='A double turquoise badge for earning 6M validated seconds of watched video.'></img>";
    } else if ($user->bossa_total_credit >  5000000) {
        $badges .= "<img style='height:40px;' src='./wildlife_badges/bossa_5000000_Feder2DoubleAmethyst.png' title='A double amethyst badge for earning 5M validated seconds of watched video.'></img>";
    } else if ($user->bossa_total_credit >  4000000) {
        $badges .= "<img style='height:40px;' src='./wildlife_badges/bossa_4000000_Feder2DoubleGold.png' title='A double gold badge for earning 4M validated seconds of watched video.'></img>";
    } else if ($user->bossa_total_credit >  3000000) {
        $badges .= "<img style='height:40px;' src='./wildlife_badges/bossa_3000000_Feder2DoubleSilber.png' title='A double silver badge for earning 3M validated seconds of watched video.'></img>";
    } else if ($user->bossa_total_credit >  2500000) {
        $badges .= "<img style='height:40px;' src='./wildlife_badges/bossa_2500000_Feder2DoubleBronze.png' title='A double bronze badge for earning 2.5M validated seconds of watched video.'></img>";
    } else if ($user->bossa_total_credit >  2000000) {
        $badges .= "<img style='height:40px;' src='./wildlife_badges/bossa_2000000_FederEmerald.png' title='An emerald badge for earning 2M validated seconds of watched video.'></img>";
    } else if ($user->bossa_total_credit >  1500000) {
        $badges .= "<img style='height:40px;' src='./wildlife_badges/bossa_1500000_FederJade.png' title='A jade badge for earning 1.5M validated seconds of watched video.'></img>";
    } else if ($user->bossa_total_credit >  1000000) {
        $badges .= "<img style='height:40px;' src='./wildlife_badges/bossa_1000000_FederSaph.png' title='A sapphire badge for earning 1M validated seconds of watched video.'></img>";
    } else if ($user->bossa_total_credit >   500000) {
        $badges .= "<img style='height:40px;' src='./wildlife_badges/bossa_500000_FederTurq.png' title='A turquoise badge for earning 500k validated seconds of watched video.'></img>";
    } else if ($user->bossa_total_credit >   250000) {
        $badges .= "<img style='height:40px;' src='./wildlife_badges/bossa_250000_FederAmethyst.png' title='An amethyst badge for earning 250k validated seconds of watched video.'></img>";
    } else if ($user->bossa_total_credit >   100000) {
        $badges .= "<img style='height:40px;' src='./wildlife_badges/bossa_100000_FederGold.png' title='A gold badge for earning 100k validated seconds of watched video.'></img>";
    } else if ($user->bossa_total_credit >    50000) {
        $badges .= "<img style='height:40px;' src='./wildlife_badges/bossa_50000_FederSilber.png' title='A silver badge for earning 50k validated seconds of watched video.'></img>";
    } else if ($user->bossa_total_credit >    10000) {
        $badges .= "<img style='height:40px;' src='./wildlife_badges/bossa_10000_FederBronze.png' title='A bronze badge for earning 10k validated seconds of watched video.'></img>";
    }

    return $badges;
}

function get_badges($user) {
    $badges = "";

    if ($user->bossa_total_credit > 10000) {
        $badges .= "&nbsp;&nbsp;";
        $badges .= get_bossa_badge($user);
    }

    if ($user->total_credit > 10000) {
        $badges .= "&nbsp;&nbsp;";
        $badges .= get_credit_badge($user);
    }

    return $badges;
}

?>
