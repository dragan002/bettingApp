<?php

namespace Database\Seeders;

use App\Models\Player;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoPlayersSeeder extends Seeder
{
    public function run(): void
    {
        $pin = Hash::make('1234');

        $names = [
            'Kombinator', 'Analitičar', 'Tipster', 'Prorok', 'Špekulant',
            'Mudrac', 'Lutrija', 'Bingo', 'Ekspert', 'Guru',
            'Majstor', 'Vizionar', 'Strateg', 'Taktičar', 'Genij',
            'Filozof', 'Veteran', 'Šampion', 'Legenda', 'Kapetan',
            'Gazda', 'Bos', 'Šef', 'Džoker', 'Favorit',
            'Autsajder', 'Meraklija', 'Cafedžija', 'Selektor', 'Trener',
            'Navijač', 'Fanatik', 'Ultra', 'Šerif', 'Direktor',
            'Menadžer', 'Doktor', 'Profesor', 'Frajer', 'Špicer',
            'Stari Mudrac', 'Mladi Kombinator', 'Večni Prorok', 'Veliki Špekulant', 'Ludi Tipster',
            'Pijani Strateg', 'Trezni Guru', 'Famozni Ekspert', 'Legendarni Bos', 'Divlji Fanatik',
            'Mirni Filozof', 'Brzi Kombinator', 'Spori Analitičar', 'Hitri Vizionar', 'Mudri Veteran',
            'Srećni Džoker', 'Nesrećni Favorit', 'Bogati Gazda', 'Gladni Šampion', 'Umoran Selektor',
            'Stari Cafedžija', 'Mladi Navijač', 'Večni Autsajder', 'Veliki Majstor', 'Ludi Kapetan',
            'Pijani Šerif', 'Trezni Trener', 'Famozni Strateg', 'Legendarni Meraklija', 'Divlji Ultra',
            'Mirni Direktor', 'Brzi Profesor', 'Spori Doktor', 'Hitri Menadžer', 'Mudri Frajer',
            'Srećni Špicer', 'Nesrećni Ekspert', 'Bogati Tipster', 'Gladni Prorok', 'Umoran Genij',
            'Kombinator Stari', 'Analitičar Pravi', 'Tipster Pravi', 'Prorok Stari', 'Špekulant Veliki',
            'Mudrac Pravi', 'Lutrija Prava', 'Bingo Pravi', 'Ekspert Pravi', 'Guru Stari',
            'Majstor Pravi', 'Vizionar Pravi', 'Strateg Pravi', 'Taktičar Stari', 'Genij Pravi',
            'Filozof Pravi', 'Veteran Pravi', 'Šampion Pravi', 'Legenda Prava', 'Kapetan Stari',
            'Džepni Kombinator', 'Kafanski Prorok', 'Lokalni Ekspert', 'Kvartovski Guru', 'Ulični Strateg',
            'Kafanski Mudrac', 'Lokalni Šampion', 'Kvartovski Veteran', 'Ulični Kapetan', 'Džepni Bos',
            'Kafanski Vizionar', 'Lokalni Taktičar', 'Kvartovski Genij', 'Ulični Filozof', 'Džepni Šef',
            'Kafanski Majstor', 'Lokalni Fanatik', 'Kvartovski Navijač', 'Ulični Selektor', 'Džepni Trener',
            'Noćni Kombinator', 'Jutarnji Prorok', 'Večernji Ekspert', 'Podnevni Guru', 'Ponoćni Strateg',
            'Noćni Mudrac', 'Jutarnji Šampion', 'Večernji Veteran', 'Podnevni Kapetan', 'Ponoćni Bos',
            'Noćni Vizionar', 'Jutarnji Taktičar', 'Večernji Genij', 'Podnevni Filozof', 'Ponoćni Šef',
            'Noćni Majstor', 'Jutarnji Fanatik', 'Večernji Navijač', 'Podnevni Selektor', 'Ponoćni Trener',
            'Balkanski Kombinator', 'Bosanski Prorok', 'Regionalni Ekspert', 'Domaći Guru', 'Lokalni Džoker',
            'Balkanski Mudrac', 'Bosanski Šampion', 'Regionalni Veteran', 'Domaći Kapetan', 'Lokalni Bos',
            'Balkanski Vizionar', 'Bosanski Taktičar', 'Regionalni Genij', 'Domaći Filozof', 'Lokalni Šef',
            'Balkanski Majstor', 'Bosanski Fanatik', 'Regionalni Navijač', 'Domaći Selektor', 'Lokalni Trener',
            'Skriveni Kombinator', 'Tajni Prorok', 'Anonimni Ekspert', 'Nepoznati Guru', 'Misteriozni Strateg',
            'Skriveni Mudrac', 'Tajni Šampion', 'Anonimni Veteran', 'Nepoznati Kapetan', 'Misteriozni Bos',
            'Skriveni Vizionar', 'Tajni Taktičar', 'Anonimni Genij', 'Nepoznati Filozof', 'Misteriozni Šef',
            'Skriveni Majstor', 'Tajni Fanatik', 'Anonimni Navijač', 'Nepoznati Selektor', 'Misteriozni Trener',
            'Kombinator Junior', 'Prorok Senior', 'Ekspert Jr', 'Guru Sr', 'Strateg II',
            'Mudrac III', 'Šampion IV', 'Veteran V', 'Kapetan VI', 'Bos VII',
            'Vizionar VIII', 'Taktičar IX', 'Genij X', 'Filozof XI', 'Šef XII',
            'Majstor XIII', 'Fanatik XIV', 'Navijač XV', 'Selektor XVI', 'Trener XVII',
            'Kombinator 2000', 'Prorok 2001', 'Ekspert 2002', 'Guru 2003', 'Strateg 2004',
            'Mudrac 2005', 'Šampion 2006', 'Veteran 2007', 'Kapetan 2008', 'Bos 2009',
            'Vizionar 2010', 'Taktičar 2011', 'Genij 2012', 'Filozof 2013', 'Šef 2014',
            'Majstor 2015', 'Fanatik 2016', 'Navijač 2017', 'Selektor 2018', 'Trener 2019',
            'El Kombinator', 'El Prorok', 'El Ekspert', 'El Guru', 'El Strateg',
            'El Mudrac', 'El Šampion', 'El Veteran', 'El Kapetan', 'El Bos',
            'El Vizionar', 'El Taktičar', 'El Genij', 'El Filozof', 'El Šef',
            'El Majstor', 'El Fanatik', 'El Navijač', 'El Selektor', 'El Trener',
        ];

        foreach ($names as $name) {
            Player::firstOrCreate(
                ['name' => $name],
                ['pin' => $pin, 'is_admin' => false, 'token_balance' => 0]
            );
        }
    }
}
