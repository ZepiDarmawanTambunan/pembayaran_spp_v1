<?php

namespace App\Charts;

use ArielMejiaDev\LarapexCharts\LarapexChart;
use App\Models\Ananda;

class AnandaKelompokChart
{
    protected $chart;

    public function __construct(LarapexChart $chart)
    {
        $this->chart = $chart;
    }
    public function build(): \ArielMejiaDev\LarapexCharts\DonutChart
    {
        $anandaKelompok = Ananda::orderBy('kelompok')->get();
        $data = [];
        $label = [];
        foreach (getNamaKelompok() as $key => $value) {
            array_push($data, $anandaKelompok->where('kelompok', $value)->count());
            array_push($label, 'Kelompok '.$value);
        }
        return $this->chart->donutChart()
            ->setTitle('Data Ananda PerKelompok')
            ->setWidth(300)
            ->setHeight(300)
            ->setSubtitle(date('Y'))
            ->addData($data)
            ->setLabels($label);
    }
}
