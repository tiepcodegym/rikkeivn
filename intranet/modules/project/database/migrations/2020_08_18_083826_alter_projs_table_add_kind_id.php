<?php

use Illuminate\Database\Migrations\Migration;
use Rikkei\Api\Helper\Operation;

class AlterProjsTableAddKindId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $internal = array(
            'Division Trainning',
            'Rikkei Konbini',
            'myRikkei_Phase 2',
            'RikkeiJP_Portal',
            'myRikkei_Phase1',
            'TQC Danang',
            'Facial Tracking And Recognize Processing',
            'Rikkei AI Homepage',
            'Rikkei IM',
            'Intranet_HCM',
            'TQC Project 2019',
            'myRikkei_Phase 3',
            'myRikkei_phase 4',
            'Rikkei PMS',
            'D2 Bóc Băng AI',
            'BPO Annotation Tool',
            'Rikkei Tool Maintenance',
            'Platform for Robot',
            'Chatbot Platform',
            'PQA_HN',
            'Rikkeisoft intranet maintain',
            'Rikkei Auth Server',
            'Rikkei HRM'
        );

        $offshoreEN = array(
            '[Kyocera] OpenGL App',
            'KCSG-Rikkeisoft UNIQLO HHT System',
            'Chomp',
            'Palettize HHT',
            'Maintenance Chemical Production System',
            'Redseal Phase 2',
            'KCSG-Rikkeisoft EDI System',
            'AIC - Tiện ích Nhà đầu tư',
            'NewBalance-Phase2'
        );

        $offshoreJP = array(
            'Sakamoto-Shiryou',
            'Clipho-OSDC',
            'Web EDI',
            'Sunloft_Suisan',
            'S_IOTCowAnalysis',
            'Kidsline',
            'S_Annotation',
            'EC SBPS',
            'Canbus mobile phase 3',
            'NEC',
            'CC_Ispeed',
            'S_KonekoAndroid',
            'ST.Net Form',
            'NTK_Migration',
            'Canbus Phase 12',
            'OSDC CBIT WEB',
            'Loft-Line',
            'Feliz_iOS_Swift4.2_Update',
            'AnshinLightOSDC',
            'C Project',
            'E-Novate Form System',
            'AnshinLight',
            'Reas_LiveDe55',
            'S_Resort2020',
            'METRAN_JPAP',
            '[Toukei]_s_iNepiForce',
            'S_Kony',
            'EC Larvel API',
            'E-Novate Seminar',
            'KPIS-PointEX(Phrase3)',
            'Máu_Khó_Đông',
            'Hanbai_App',
            'ISP_Labo',
            'Canbus phase 13',
            'KPIS-PointEX(Phase4)-WalletWebApp',
            'SBアシストアプリ',
            'OSDC AngularJS D8',
            'KPIS-PointEX(Phase2)',
            'S_Osaka_TRASAS',
            'STNET_IT',
            'Sunloft_2018_OSDC_Ruby_Phase2',
            'Sunloft-Yoshikei',
            'JPAP_CLOUD_PH2',
            'Uniform EC Site',
            'OSDC Python Sunloft',
            'ME_ContentsShare',
            'K_MaaS',
            'S_Softbank_backend',
            'OM_CrossPlatform',
            'Clouds Tumix',
            'ＶＢマイグレーション',
            'Harima Bussan',
            'S_IOTMobicollect',
            'YMT',
            'OrderInput Generation',
            'Cloco MF (TTFH)',
            'Canbus Maintain 2020',
            'MyMagazine - Phase 3',
            'IT Management step 3',
            'Kubota System',
            'Outbound_ASNO',
            'ROBOT2',
            'Hoikuen PRJ',
            'Hokanryo system',
            'daiso',
            'Cloco_OSDC',
            'Cloud Step 2020',
            'DNP phase 2',
            'Xtheta Site',
            'canbus mobile phase 4',
            'DeviceShare',
            'Yoshikei-Customer Management',
            'OSDC Ruby Cloco',
            'HOP-Yoochien',
            'Kony 2020',
            'Clipho App',
            'ZDC-Car Share',
            'Clipho web',
            'OM_NeoTENS',
            'T-DECS',
            'Vehicle Aftercare Cloud',
            'Surf-Snow',
            'CATIA project',
            'ProvisionAutomationTestcafe',
            'TIME-3',
            'Tokei_KeibiCenter_Doc',
            'TalentPowerRanking',
            'Sun_2018_OSDC_Passtell_Phase2',
            'S_InfobankMobileDevelopment',
            'Omusubi',
            'Advance Manager version 2.0',
            'ProVisionAgentSystem',
            'Harima Phase 2',
            'Hokkaido Inspection system',
            'KDL_Workflow_System',
            'PHP Version Up',
            'UnicornLive',
            'Ec Avex',
            'TCC_iNepiForce_P4',
            'HIP',
            'HKH-M1',
            'S_NTT-TX',
            'nearMe-Q32019',
            'SO_TeamSol',
            'MOTEX labor',
            'Crane Game Phase 2',
            'ASNO Order Management System',
            'ShizuokaNishi Inspection system',
            'Sunloft-Suzunari',
            'ClearWorks',
            'RIP Project',
            'Sunloft-CarSystem',
            'LiveDe55 Phase 2',
            'OSDC CBIT BACS',
            'Sunloft-Jacpa',
            'OSDC CBIT WAIWAINET',
            'Secure Chat',
            'CBIT_CMP',
            'Export Report System',
            'Systena America Test',
            'Provision_PointCard'
        );

        $offshoreVN = array(
            'App Tết 2020',
            'Y tế 4.0',
            'Fujikin VN Measure System',
            'TEECOIN',
            'MCD Phase 2',
            'DISPRO',
            'Taxi Operating System',
            'AIC - Molisa 3s',
            'E-Cabinet',
            'Hy Vong Japan',
            'AIC - Tiện ích Doanh nghiệp',
            'AIC XKLĐ',
            'AIC - Tiện ích người dân',
            'Bóc băng'
        );

        $onsiteJP = array(
            'Systena JP onsite',
            '2019_ĐN Onsite',
            'Onsite TOKO OA',
            'Clouds_Automotive',
            'Onsite D6'
        );

        $onsiteVN = array(
            'VHT/TCDT - RMS',
            'Onsite Topica D2',
            'VTS_ID',
            'Onsite Panasonic D1',
            'VHT/CHDK - HQ2019',
            'VTS - Quản lý trường học',
            'VTS/HN - Camera2019',
            'Viettel ANM',
            'onsite Savvycom D2',
            'D3 D5 onsite AltPlus',
            'TPF_onsite',
            'VTMap_ODC',
            'Onsite Savvycom',
            'onsite Altplus D2',
            'MyParking_P2',
            'HCM_Onsiters',
            'VHT/TCDT - VHCR',
            'onsite V-Office VTS',
            'OCS 4.0',
            'Onsite Testing VHT-OCS',
            '[Onsite VHT] Y te Thong Minh',
            'Onsite VTS BQP',
            'Onsite Viettel ANM',
            'D2 Onsite NEC VN',
            'Onsite testing VT VTS (nhóm dự án)',
            'Onsite Panasonic',
            'Onsite Panasonic 2'
        );

        DB::table('projs')->whereIn('name', $internal)->update(['kind_id' => Operation::KIND_INTERNAL]);
        DB::table('projs')->whereIn('name', $offshoreEN)->update(['kind_id' => Operation::KIND_OFFSHORE_EN]);
        DB::table('projs')->whereIn('name', $offshoreJP)->update(['kind_id' => Operation::KIND_OFFSHORE_JP]);
        DB::table('projs')->whereIn('name', $offshoreVN)->update(['kind_id' => Operation::KIND_OFFSHORE_VN]);
        DB::table('projs')->whereIn('name', $onsiteJP)->update(['kind_id' => Operation::KIND_ONSITE_JP]);
        DB::table('projs')->whereIn('name', $onsiteVN)->update(['kind_id' => Operation::KIND_ONSITE_VN]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
