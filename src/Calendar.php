<?php

namespace Kindy\HijriDate;


/**
 * 
 * This file is part of the Kindy\HijriDate Package.
 *
 * (c) Ibrahim Abotaleb <admin@mrkindy.com> and Saeed Hubaishan
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 * Hijri Calendar Class is group of functions that gets the Hijri Calendar and converts it to other calendars
 *
 * This class contains functions work similar to calendar functions in PHP but
 * it is work with Hijri Calendar.
 * 
 */
class Calendar
{
	
	/**
	 *
	 * @ignore
	 *
	 */
	protected static $s_umdata;
	/**
	 *
	 * @ignore
	 *
	 */
	protected static $s_adjdata;
	/**
	 *
	 * @var int[] The Um Al-Qura Data
	 */
	protected $umdata;
	
	/**
	 *
	 * @var bool TRUE to use Um Al-Qura algorithm, FALSE to use Hijri Tabular Algorithm
	 */
	public $umalqura = TRUE;
	
	/**
	 *
	 * @var array the Um AlQura adjustment data, keys are month indexes from umstartyear, values are modified Julian day
	 */
	protected $adj_data;
	
	/**
	 * the Um Al-Qura Calendar start year
	 */
	const umstartyear = 1318;
	/**
	 * the Um Al-Qura Calendar end year
	 */
	const umendyear = 1500;
	/**
	 * the Um Al-Qura Calendar start Julian day
	 */
	const umstartjd = 2415140;
	/**
	 * the Um Al-Qura Calendar end Julian day
	 */
	const umendjd = 2479960;
	
	/**
	 * int factor for convert julianday to modified julianday
	 */
	const mjd_factor = 2400000;
	
	/**
	 *
	 * @static
	 *
	 * @var array Array of supported langcodes
	 */
	public static $supported_languages = array('ar', 'en', 'fa', 'ur', 'sq', 'az', 'bn', 'bs', 'zh', 'fr', 'de', 'hi', 'id', 'ckb', 'ms', 'ru', 'es', 'tr', 'pt', 'it');

	/**
	 * Create new hijri\Calendar object according to given settings
	 *
	 * @global array $hijri_settings global hijri calendar settings
	 * @param array $settings
	 *        	Optional array contains one or more settings of the hijri
	 *        	calendar object these settings are:
	 *        	<dt>umalqura:bool</dt> <dd> TRUE to use Um AlQura algorithm, FALSE to use Hijri Tabular algorithm</dd>
	 *        	<dt>adj_data:string|array</dt> <dd>string: contains Um Al-Qura adjustment data that got by function get_adjdata(TRUE) of CalendarAdjustment class
	 *        	or array contains Um Al-Qura adjustment data that got by function get_adjdata(FALSE)</dd>
	 *        	if not set, the defaults from $hijri_settings global variable will be used.
	 * @return Calendar hijri\Calendar object with the specified settings.
	 */
	public function __construct($settings = array())
	{
		global $hijri_settings;
		$this->adj_data = array();
		if (!empty($hijri_settings)) {
			$settings = array_replace($hijri_settings, $settings);
		}
		if (!empty($settings)) {
			if (isset($settings['umalqura'])) {
				$this->umalqura = $settings['umalqura'];
			}
			if (isset($settings['adj_data'])) {
				if (is_array($settings['adj_data'])) {
					$this->adj_data = $settings['adj_data'];
				} else {
					$unser = json_decode($settings['adj_data'], TRUE);
					if (is_array($unser)) {
						$this->adj_data = $unser;
					}
				}
			}
		}
	}

	/**
	 * Loads Um Al-Qura data and apply the adjustments
	 *
	 * @param boolean $with_adj
	 *        	TRUE (default) to apply adjustments, FALSE to not
	 * @param bool $return_array
	 *        	True to return umdata array, False to load umdata to $umdata variable
	 * @return array|bool array contains Um Al-Qura data if $return_array =true, bool true else
	 */
	protected function get_umalquradata($with_adj = TRUE, $return_array = FALSE)
	{
		if (!empty(self::$s_umdata) && (($with_adj && ($this->adj_data == self::$s_adjdata)) || (!$with_adj && (self::$s_adjdata == array())))) {
			$myumdata = self::$s_umdata;
		} else {
			$myumdata = array(
				15140, 15169, 15199, 15228, 15258, 15287, 15317, 15347, 15377, 15406, 15436, 15465, 15494, 15524, 15553, 15582, 15612, 15641, 15671, 15701, 15731, 15760, 15790, 15820, 15849, 15878, 15908, 15937, 15966, 15996, 16025, 16055, 16085, 16114, 16144, 16174, 16204, 16233,
				16262, 16292, 16321, 16350, 16380, 16409, 16439, 16468, 16498, 16528, 16558, 16587, 16617, 16646, 16676, 16705, 16734, 16764, 16793, 16823, 16852, 16882, 16912, 16941, 16971, 17001, 17030, 17060, 17089, 17118, 17148, 17177, 17207, 17236, 17266, 17295, 17325, 17355, 17384, 17414,
				17444, 17473, 17502, 17532, 17561, 17591, 17620, 17650, 17679, 17709, 17738, 17768, 17798, 17827, 17857, 17886, 17916, 17945, 17975, 18004, 18034, 18063, 18093, 18122, 18152, 18181, 18211, 18241, 18270, 18300, 18330, 18359, 18388, 18418, 18447, 18476, 18506, 18535, 18565, 18595,
				18625, 18654, 18684, 18714, 18743, 18772, 18802, 18831, 18860, 18890, 18919, 18949, 18979, 19008, 19038, 19068, 19098, 19127, 19156, 19186, 19215, 19244, 19274, 19303, 19333, 19362, 19392, 19422, 19452, 19481, 19511, 19540, 19570, 19599, 19628, 19658, 19687, 19717, 19746, 19776,
				19806, 19836, 19865, 19895, 19924, 19954, 19983, 20012, 20042, 20071, 20101, 20130, 20160, 20190, 20219, 20249, 20279, 20308, 20338, 20367, 20396, 20426, 20455, 20485, 20514, 20544, 20573, 20603, 20633, 20662, 20692, 20721, 20751, 20780, 20810, 20839, 20869, 20898, 20928, 20957,
				20987, 21016, 21046, 21076, 21105, 21135, 21164, 21194, 21223, 21253, 21282, 21312, 21341, 21371, 21400, 21430, 21459, 21489, 21519, 21548, 21578, 21607, 21637, 21666, 21696, 21725, 21754, 21784, 21813, 21843, 21873, 21902, 21932, 21962, 21991, 22021, 22050, 22080, 22109, 22138,
				22168, 22197, 22227, 22256, 22286, 22316, 22346, 22375, 22405, 22434, 22464, 22493, 22522, 22552, 22581, 22611, 22640, 22670, 22700, 22730, 22759, 22789, 22818, 22848, 22877, 22906, 22936, 22965, 22994, 23024, 23054, 23083, 23113, 23143, 23173, 23202, 23232, 23261, 23290, 23320,
				23349, 23379, 23408, 23438, 23467, 23497, 23527, 23556, 23586, 23616, 23645, 23674, 23704, 23733, 23763, 23792, 23822, 23851, 23881, 23910, 23940, 23970, 23999, 24029, 24058, 24088, 24117, 24147, 24176, 24206, 24235, 24265, 24294, 24324, 24353, 24383, 24413, 24442, 24472, 24501,
				24531, 24560, 24590, 24619, 24648, 24678, 24707, 24737, 24767, 24796, 24826, 24856, 24885, 24915, 24944, 24974, 25003, 25032, 25062, 25091, 25121, 25150, 25180, 25210, 25240, 25269, 25299, 25328, 25358, 25387, 25416, 25446, 25475, 25505, 25534, 25564, 25594, 25624, 25653, 25683,
				25712, 25742, 25771, 25800, 25830, 25859, 25888, 25918, 25948, 25977, 26007, 26037, 26067, 26096, 26126, 26155, 26184, 26214, 26243, 26272, 26302, 26332, 26361, 26391, 26421, 26451, 26480, 26510, 26539, 26568, 26598, 26627, 26656, 26686, 26715, 26745, 26775, 26805, 26834, 26864,
				26893, 26923, 26952, 26982, 27011, 27041, 27070, 27099, 27129, 27159, 27188, 27218, 27248, 27277, 27307, 27336, 27366, 27395, 27425, 27454, 27484, 27513, 27542, 27572, 27602, 27631, 27661, 27691, 27720, 27750, 27779, 27809, 27838, 27868, 27897, 27926, 27956, 27985, 28015, 28045,
				28074, 28104, 28134, 28163, 28193, 28222, 28252, 28281, 28310, 28340, 28369, 28399, 28428, 28458, 28488, 28517, 28547, 28577, 28606, 28636, 28665, 28694, 28724, 28753, 28782, 28812, 28842, 28871, 28901, 28931, 28961, 28990, 29020, 29049, 29078, 29108, 29137, 29166, 29196, 29226,
				29255, 29285, 29315, 29345, 29374, 29404, 29433, 29462, 29492, 29521, 29550, 29580, 29609, 29639, 29669, 29699, 29728, 29758, 29788, 29817, 29846, 29876, 29905, 29934, 29964, 29993, 30023, 30053, 30082, 30112, 30142, 30171, 30201, 30230, 30260, 30289, 30319, 30348, 30377, 30407,
				30436, 30466, 30496, 30525, 30555, 30585, 30614, 30644, 30673, 30703, 30732, 30761, 30791, 30820, 30850, 30879, 30909, 30939, 30968, 30998, 31028, 31057, 31087, 31116, 31146, 31175, 31204, 31234, 31263, 31293, 31322, 31352, 31382, 31411, 31441, 31471, 31500, 31530, 31559, 31588,
				31618, 31647, 31677, 31706, 31736, 31765, 31795, 31825, 31855, 31884, 31914, 31943, 31972, 32002, 32031, 32060, 32090, 32119, 32149, 32179, 32209, 32239, 32268, 32298, 32327, 32356, 32386, 32415, 32444, 32474, 32503, 32533, 32563, 32593, 32622, 32652, 32682, 32711, 32740, 32770,
				32799, 32828, 32858, 32887, 32917, 32947, 32976, 33006, 33036, 33065, 33095, 33124, 33154, 33183, 33212, 33242, 33271, 33301, 33330, 33360, 33390, 33420, 33449, 33479, 33508, 33538, 33567, 33596, 33626, 33655, 33685, 33714, 33744, 33774, 33803, 33833, 33862, 33892, 33922, 33951,
				33981, 34010, 34039, 34069, 34098, 34128, 34157, 34187, 34216, 34246, 34276, 34305, 34335, 34365, 34394, 34424, 34453, 34482, 34512, 34541, 34571, 34600, 34630, 34660, 34689, 34719, 34749, 34778, 34808, 34837, 34866, 34896, 34925, 34954, 34984, 35014, 35043, 35073, 35103, 35133,
				35162, 35192, 35221, 35250, 35280, 35309, 35338, 35368, 35397, 35427, 35457, 35487, 35516, 35546, 35576, 35605, 35634, 35664, 35693, 35722, 35752, 35781, 35811, 35841, 35870, 35900, 35930, 35959, 35989, 36018, 36048, 36077, 36106, 36136, 36165, 36195, 36224, 36254, 36284, 36314,
				36343, 36373, 36402, 36432, 36461, 36490, 36520, 36549, 36579, 36608, 36638, 36668, 36697, 36727, 36757, 36786, 36816, 36845, 36874, 36904, 36933, 36963, 36992, 37022, 37051, 37081, 37111, 37140, 37170, 37199, 37229, 37258, 37288, 37317, 37347, 37376, 37406, 37435, 37465, 37494,
				37524, 37554, 37583, 37613, 37642, 37672, 37702, 37731, 37760, 37790, 37819, 37848, 37878, 37908, 37937, 37967, 37997, 38026, 38056, 38086, 38115, 38144, 38174, 38203, 38232, 38262, 38291, 38321, 38351, 38381, 38410, 38440, 38470, 38499, 38528, 38558, 38587, 38616, 38646, 38675,
				38705, 38735, 38764, 38794, 38824, 38854, 38883, 38912, 38942, 38971, 39000, 39030, 39059, 39089, 39118, 39148, 39178, 39208, 39237, 39267, 39296, 39326, 39355, 39384, 39414, 39443, 39473, 39502, 39532, 39562, 39591, 39621, 39651, 39680, 39710, 39739, 39768, 39798, 39827, 39857,
				39886, 39916, 39945, 39975, 40005, 40034, 40064, 40093, 40123, 40152, 40182, 40211, 40241, 40270, 40300, 40329, 40359, 40388, 40418, 40448, 40477, 40507, 40536, 40566, 40595, 40625, 40654, 40684, 40713, 40743, 40772, 40802, 40831, 40861, 40891, 40920, 40950, 40979, 41009, 41038,
				41068, 41097, 41126, 41156, 41185, 41215, 41245, 41275, 41304, 41334, 41364, 41393, 41422, 41452, 41481, 41510, 41540, 41569, 41599, 41629, 41658, 41688, 41718, 41748, 41777, 41806, 41836, 41865, 41894, 41924, 41953, 41983, 42012, 42042, 42072, 42102, 42131, 42161, 42190, 42220,
				42249, 42278, 42308, 42337, 42367, 42396, 42426, 42456, 42485, 42515, 42545, 42574, 42604, 42633, 42662, 42692, 42721, 42751, 42780, 42810, 42839, 42869, 42899, 42929, 42958, 42988, 43017, 43046, 43076, 43105, 43135, 43164, 43194, 43223, 43253, 43283, 43312, 43342, 43371, 43401,
				43430, 43460, 43489, 43519, 43548, 43578, 43607, 43637, 43666, 43696, 43726, 43755, 43785, 43814, 43844, 43873, 43903, 43932, 43962, 43991, 44021, 44050, 44080, 44109, 44139, 44169, 44198, 44228, 44257, 44287, 44316, 44346, 44375, 44404, 44434, 44463, 44493, 44523, 44552, 44582,
				44612, 44641, 44671, 44700, 44730, 44759, 44788, 44818, 44847, 44877, 44906, 44936, 44966, 44996, 45025, 45055, 45084, 45114, 45143, 45172, 45202, 45231, 45261, 45290, 45320, 45350, 45380, 45409, 45439, 45468, 45498, 45527, 45556, 45586, 45615, 45644, 45674, 45704, 45733, 45763,
				45793, 45823, 45852, 45882, 45911, 45940, 45970, 45999, 46028, 46058, 46088, 46117, 46147, 46177, 46206, 46236, 46265, 46295, 46324, 46354, 46383, 46413, 46442, 46472, 46501, 46531, 46560, 46590, 46620, 46649, 46679, 46708, 46738, 46767, 46797, 46826, 46856, 46885, 46915, 46944,
				46974, 47003, 47033, 47063, 47092, 47122, 47151, 47181, 47210, 47240, 47269, 47298, 47328, 47357, 47387, 47417, 47446, 47476, 47506, 47535, 47565, 47594, 47624, 47653, 47682, 47712, 47741, 47771, 47800, 47830, 47860, 47890, 47919, 47949, 47978, 48008, 48037, 48066, 48096, 48125,
				48155, 48184, 48214, 48244, 48273, 48303, 48333, 48362, 48392, 48421, 48450, 48480, 48509, 48538, 48568, 48598, 48627, 48657, 48687, 48717, 48746, 48776, 48805, 48834, 48864, 48893, 48922, 48952, 48982, 49011, 49041, 49071, 49100, 49130, 49160, 49189, 49218, 49248, 49277, 49306,
				49336, 49365, 49395, 49425, 49455, 49484, 49514, 49543, 49573, 49602, 49632, 49661, 49690, 49720, 49749, 49779, 49809, 49838, 49868, 49898, 49927, 49957, 49986, 50016, 50045, 50075, 50104, 50133, 50163, 50192, 50222, 50252, 50281, 50311, 50340, 50370, 50400, 50429, 50459, 50488,
				50518, 50547, 50576, 50606, 50635, 50665, 50694, 50724, 50754, 50784, 50813, 50843, 50872, 50902, 50931, 50960, 50990, 51019, 51049, 51078, 51108, 51138, 51167, 51197, 51227, 51256, 51286, 51315, 51345, 51374, 51403, 51433, 51462, 51492, 51522, 51552, 51582, 51611, 51641, 51670,
				51699, 51729, 51758, 51787, 51816, 51846, 51876, 51906, 51936, 51965, 51995, 52025, 52054, 52083, 52113, 52142, 52171, 52200, 52230, 52260, 52290, 52319, 52349, 52379, 52408, 52438, 52467, 52497, 52526, 52555, 52585, 52614, 52644, 52673, 52703, 52733, 52762, 52792, 52822, 52851,
				52881, 52910, 52939, 52969, 52998, 53028, 53057, 53087, 53116, 53146, 53176, 53205, 53235, 53264, 53294, 53324, 53353, 53383, 53412, 53441, 53471, 53500, 53530, 53559, 53589, 53619, 53648, 53678, 53708, 53737, 53767, 53796, 53825, 53855, 53884, 53914, 53943, 53973, 54003, 54032,
				54062, 54092, 54121, 54151, 54180, 54209, 54239, 54268, 54297, 54327, 54357, 54387, 54416, 54446, 54476, 54505, 54535, 54564, 54593, 54623, 54652, 54681, 54711, 54741, 54770, 54800, 54830, 54859, 54889, 54919, 54948, 54977, 55007, 55036, 55066, 55095, 55125, 55154, 55184, 55213,
				55243, 55273, 55302, 55332, 55361, 55391, 55420, 55450, 55479, 55508, 55538, 55567, 55597, 55627, 55657, 55686, 55716, 55745, 55775, 55804, 55834, 55863, 55892, 55922, 55951, 55981, 56011, 56040, 56070, 56100, 56129, 56159, 56188, 56218, 56247, 56276, 56306, 56335, 56365, 56394,
				56424, 56454, 56483, 56513, 56543, 56572, 56601, 56631, 56660, 56690, 56719, 56749, 56778, 56808, 56837, 56867, 56897, 56926, 56956, 56985, 57015, 57044, 57074, 57103, 57133, 57162, 57192, 57221, 57251, 57280, 57310, 57340, 57369, 57399, 57429, 57458, 57487, 57517, 57546, 57576,
				57605, 57634, 57664, 57694, 57723, 57753, 57783, 57813, 57842, 57871, 57901, 57930, 57959, 57989, 58018, 58048, 58077, 58107, 58137, 58167, 58196, 58226, 58255, 58285, 58314, 58343, 58373, 58402, 58432, 58461, 58491, 58521, 58551, 58580, 58610, 58639, 58669, 58698, 58727, 58757,
				58786, 58816, 58845, 58875, 58905, 58934, 58964, 58994, 59023, 59053, 59082, 59111, 59141, 59170, 59200, 59229, 59259, 59288, 59318, 59348, 59377, 59407, 59436, 59466, 59495, 59525, 59554, 59584, 59613, 59643, 59672, 59702, 59731, 59761, 59791, 59820, 59850, 59879, 59909, 59939,
				59968, 59997, 60027, 60056, 60086, 60115, 60145, 60174, 60204, 60234, 60264, 60293, 60323, 60352, 60381, 60411, 60440, 60469, 60499, 60528, 60558, 60588, 60618, 60647, 60677, 60707, 60736, 60765, 60795, 60824, 60853, 60883, 60912, 60942, 60972, 61002, 61031, 61061, 61090, 61120,
				61149, 61179, 61208, 61237, 61267, 61296, 61326, 61356, 61385, 61415, 61445, 61474, 61504, 61533, 61563, 61592, 61621, 61651, 61680, 61710, 61739, 61769, 61799, 61828, 61858, 61888, 61917, 61947, 61976, 62006, 62035, 62064, 62094, 62123, 62153, 62182, 62212, 62242, 62271, 62301,
				62331, 62360, 62390, 62419, 62448, 62478, 62507, 62537, 62566, 62596, 62625, 62655, 62685, 62715, 62744, 62774, 62803, 62832, 62862, 62891, 62921, 62950, 62980, 63009, 63039, 63069, 63099, 63128, 63157, 63187, 63216, 63246, 63275, 63305, 63334, 63363, 63393, 63423, 63453, 63482,
				63512, 63541, 63571, 63600, 63630, 63659, 63689, 63718, 63747, 63777, 63807, 63836, 63866, 63895, 63925, 63955, 63984, 64014, 64043, 64073, 64102, 64131, 64161, 64190, 64220, 64249, 64279, 64309, 64339, 64368, 64398, 64427, 64457, 64486, 64515, 64545, 64574, 64603, 64633, 64663,
				64692, 64722, 64752, 64782, 64811, 64841, 64870, 64899, 64929, 64958, 64987, 65017, 65047, 65076, 65106, 65136, 65166, 65195, 65225, 65254, 65283, 65313, 65342, 65371, 65401, 65431, 65460, 65490, 65520, 65549, 65579, 65608, 65638, 65667, 65697, 65726, 65755, 65785, 65815, 65844,
				65874, 65903, 65933, 65963, 65992, 66022, 66051, 66081, 66110, 66140, 66169, 66199, 66228, 66258, 66287, 66317, 66346, 66376, 66405, 66435, 66465, 66494, 66524, 66553, 66583, 66612, 66641, 66671, 66700, 66730, 66760, 66789, 66819, 66849, 66878, 66908, 66937, 66967, 66996, 67025,
				67055, 67084, 67114, 67143, 67173, 67203, 67233, 67262, 67292, 67321, 67351, 67380, 67409, 67439, 67468, 67497, 67527, 67557, 67587, 67617, 67646, 67676, 67705, 67735, 67764, 67793, 67823, 67852, 67882, 67911, 67941, 67971, 68000, 68030, 68060, 68089, 68119, 68148, 68177, 68207,
				68236, 68266, 68295, 68325, 68354, 68384, 68414, 68443, 68473, 68502, 68532, 68561, 68591, 68620, 68650, 68679, 68708, 68738, 68768, 68797, 68827, 68857, 68886, 68916, 68946, 68975, 69004, 69034, 69063, 69092, 69122, 69152, 69181, 69211, 69240, 69270, 69300, 69330, 69359, 69388,
				69418, 69447, 69476, 69506, 69535, 69565, 69595, 69624, 69654, 69684, 69713, 69743, 69772, 69802, 69831, 69861, 69890, 69919, 69949, 69978, 70008, 70038, 70067, 70097, 70126, 70156, 70186, 70215, 70245, 70274, 70303, 70333, 70362, 70392, 70421, 70451, 70481, 70510, 70540, 70570,
				70599, 70629, 70658, 70687, 70717, 70746, 70776, 70805, 70835, 70864, 70894, 70924, 70954, 70983, 71013, 71042, 71071, 71101, 71130, 71159, 71189, 71218, 71248, 71278, 71308, 71337, 71367, 71397, 71426, 71455, 71485, 71514, 71543, 71573, 71602, 71632, 71662, 71691, 71721, 71751,
				71781, 71810, 71839, 71869, 71898, 71927, 71957, 71986, 72016, 72046, 72075, 72105, 72135, 72164, 72194, 72223, 72253, 72282, 72311, 72341, 72370, 72400, 72429, 72459, 72489, 72518, 72548, 72577, 72607, 72637, 72666, 72695, 72725, 72754, 72784, 72813, 72843, 72872, 72902, 72931,
				72961, 72991, 73020, 73050, 73080, 73109, 73139, 73168, 73197, 73227, 73256, 73286, 73315, 73345, 73375, 73404, 73434, 73464, 73493, 73523, 73552, 73581, 73611, 73640, 73669, 73699, 73729, 73758, 73788, 73818, 73848, 73877, 73907, 73936, 73965, 73995, 74024, 74053, 74083, 74113,
				74142, 74172, 74202, 74231, 74261, 74291, 74320, 74349, 74379, 74408, 74437, 74467, 74497, 74526, 74556, 74585, 74615, 74645, 74675, 74704, 74733, 74763, 74792, 74822, 74851, 74881, 74910, 74940, 74969, 74999, 75029, 75058, 75088, 75117, 75147, 75176, 75206, 75235, 75264, 75294,
				75323, 75353, 75383, 75412, 75442, 75472, 75501, 75531, 75560, 75590, 75619, 75648, 75678, 75707, 75737, 75766, 75796, 75826, 75856, 75885, 75915, 75944, 75974, 76003, 76032, 76062, 76091, 76121, 76150, 76180, 76210, 76239, 76269, 76299, 76328, 76358, 76387, 76416, 76446, 76475,
				76505, 76534, 76564, 76593, 76623, 76653, 76682, 76712, 76741, 76771, 76801, 76830, 76859, 76889, 76918, 76948, 76977, 77007, 77036, 77066, 77096, 77125, 77155, 77185, 77214, 77243, 77273, 77302, 77332, 77361, 77390, 77420, 77450, 77479, 77509, 77539, 77569, 77598, 77627, 77657,
				77686, 77715, 77745, 77774, 77804, 77833, 77863, 77893, 77923, 77952, 77982, 78011, 78041, 78070, 78099, 78129, 78158, 78188, 78217, 78247, 78277, 78307, 78336, 78366, 78395, 78425, 78454, 78483, 78513, 78542, 78572, 78601, 78631, 78661, 78690, 78720, 78750, 78779, 78808, 78838,
				78867, 78897, 78926, 78956, 78985, 79015, 79044, 79074, 79104, 79133, 79163, 79192, 79222, 79251, 79281, 79310, 79340, 79369, 79399, 79428, 79458, 79487, 79517, 79546, 79576, 79606, 79635, 79665, 79695, 79724, 79753, 79783, 79812, 79841, 79871, 79900, 79930, 79960
			);
			if ($with_adj) {
				$myumdata = array_replace($myumdata, $this->adj_data);
				if (!$return_array) {
					self::$s_umdata = $myumdata;
					self::$s_adjdata = $this->adj_data;
				}
			}
		}
		if ($return_array) {
			return $myumdata;
		} else {
			$this->umdata = $myumdata;
			return TRUE;
		}
	}

	/**
	 * Returns Hijri date from julianday
	 *
	 * @param integer $julianday
	 *        	the julianday
	 * @param integer $hy
	 *        	variable to store Hijri year
	 * @param integer $hm
	 *        	variable to store Hijri month
	 * @param integer $hd
	 *        	variable to store Hijri day in month
	 * @param integer $hz
	 *        	variable to store Hijri day in year (starting from 0)
	 * @return void
	 */
	public function jd2hijri($julianday, &$hy, &$hm, &$hd, &$hz)
	{
		if ($this->umalqura && $julianday > self::umstartjd && $julianday < self::umendjd) {
			if (empty($this->umdata)) {
				$this->get_umalquradata();
			}
			$i = (int) (($julianday - 1948438) / 29.53056) - ((self::umstartyear - 1) * 12);
			$mjd = $julianday - self::mjd_factor;
			$umdata_count = count($this->umdata);
			
			for ($i = max(0, $i); $i < $umdata_count; $i++) {
				if ($this->umdata[$i] > ($mjd)) {
					break;
				}
			}
			
			$ii = floor(($i - 1) / 12);
			$hy = self::umstartyear + $ii;
			$hm = $i - 12 * $ii;
			$hd = $mjd - $this->umdata[$i - 1] + 1;
			$hz = $mjd - $this->umdata[12 * $ii];
		} else {
			$j = $julianday + 7666;
			$n = (int) ($j / 10631);
			$j = $j - ($n * 10631);
			$j1 = $j;
			$y = (int) ($j / 354.36667);
			$j = $j - round($y * 354.36667);
			if ($j == 0) {
				$y--;
				$j = $j1 - round($y * 354.36667);
				$hz = $j;
				$hd = $j - 325;
				$hm = 12;
			} else {
				$hz = $j;
				$j += 29;
				$hm = (int) ((24 * $j) / 709);
				$hd = $j - (int) ((709 * $hm) / 24);
			}
			$hy = ($n * 30) + $y + 1;
			$hy -= 5520;
			if ($hy <= 0) {
				$hy--;
			}
		}
	}

	/**
	 * Returns Hijri Date in Format month/day/year from julianday
	 *
	 * @param integer $julianday
	 *        	the julianday
	 * @return string Hijri date in format month/day/year
	 */
	public function JDToHijri($julianday)
	{
		self::jd2hijri($julianday, $hy, $hm, $hd, $z);
		return "$hm/$hd/$hy";
	}

	/**
	 * Return Hijri Date from Gregorian date
	 *
	 * @param integer $year
	 *        	the Gregorian year
	 * @param integer $month
	 *        	the Gregorian month
	 * @param integer $day
	 *        	the Gregorian day
	 * @return array An array contains Hijri Date: 'y' key for year,'m' key for month,'d' key for day
	 */
	public function GregorianToHijri($year, $month, $day)
	{
		$j = gregoriantojd($month, $day, $year);
		$this->jd2hijri($j, $hy, $hm, $hd, $hz);
		return array('y' => $hy, 'm' => $hm, 'd' => $hd);
	}

	/**
	 * Return Gregorian Date from Hijri date
	 *
	 * @param integer $year
	 *        	the Hijri year
	 * @param integer $month
	 *        	the Hijri month
	 * @param integer $day
	 *        	the Hijri day
	 * @return array An array contains Gregorian Date: 'y' key for year,'m' key for month,'d' key for day
	 */
	public function HijriToGregorian($year, $month, $day)
	{
		$j = $this->HijriToJD($month, $day, $year);
		list($m, $d, $y) = explode('/', jdtogregorian($j));
		return array('y' => $y, 'm' => $m, 'd' => $d);
	}

	/**
	 * Returns Hijri Date from Julian date
	 *
	 * @param integer $year
	 *        	the Julian year
	 * @param integer $month
	 *        	the Julian month
	 * @param integer $day
	 *        	the Julian day
	 * @return array An array contains Hijri Date: 'y' key for year,'m' key for month,'d' key for day
	 */
	public function JulianToHijri($year, $month, $day)
	{
		$j = juliantojd($month, $day, $year);
		$this->jd2hijri($j, $hy, $hm, $hd, $hz);
		return array('y' => $hy, 'm' => $hm, 'd' => $hd);
	}

	/**
	 * Returns Julian Date from Hijri date
	 *
	 * @param integer $year
	 *        	the Hijri year
	 * @param integer $month
	 *        	the Hijri month
	 * @param integer $day
	 *        	the Hijri day
	 * @return array An array contains Julian Date: 'y' key for year,'m' key for month,'d' key for day
	 */
	public function HijriToJulian($year, $month, $day)
	{
		$j = $this->HijriToJD($month, $day, $year);
		list($m, $d, $y) = explode('/', jdtojulian($j));
		return array('y' => $y, 'm' => $m, 'd' => $d);
	}

	/**
	 * Returns Hijri Date from Western date
	 *
	 * The Western date is Julian date before 1582 and Gregorian after
	 *
	 * @param integer $year
	 *        	the Western year
	 * @param integer $month
	 *        	the Western month
	 * @param integer $day
	 *        	the Western day
	 * @return array An array contains Hijri Date: 'y' key for year,'m' key for month,'d' key for day
	 */
	public function WesternToHijri($year, $month, $day)
	{
		$j = gregoriantojd($month, $day, $year);
		if ($j < 2299161) {
			$j = juliantojd($month, $day, $year);
		}
		$this->jd2hijri($j, $hy, $hm, $hd, $hz);
		return array('y' => $hy, 'm' => $hm, 'd' => $hd);
	}

	/**
	 * Return Western Date from Hijri date
	 *
	 * The Western date is Julian date before 1582 and Gregorian after
	 *
	 * @param integer $year
	 *        	the Hijri year
	 * @param integer $month
	 *        	the Hijri month
	 * @param integer $day
	 *        	the Hijri day
	 * @return array An array contains Western Date: 'y' key for year,'m' key for month,'d' key for day
	 */
	public function HijriToWestern($year, $month, $day)
	{
		$j = $this->HijriToJD($month, $day, $year);
		if ($j > 2299160) {
			list($m, $d, $y) = explode('/', jdtogregorian($j));
		} else {
			list($m, $d, $y) = explode('/', jdtojulian($j));
		}
		return array('y' => $y, 'm' => $m, 'd' => $d);
	}

	/**
	 * Returns julianday from Hijri date
	 *
	 * @param int $month
	 *        	the Hijri month
	 * @param int $day
	 *        	the Hijri day
	 * @param int $year
	 *        	the Hijri year
	 * @return int julianday
	 */
	public function HijriToJD($month, $day, $year)
	{
		if ($this->umalqura && $year >= self::umstartyear && $year <= self::umendyear) {
			if (empty($this->umdata)) {
				$this->get_umalquradata();
			}
			$ii = $year - self::umstartyear;
			$i = $month + 12 * $ii;
			$j = $day + $this->umdata[$i - 1] - 1;
			$j += self::mjd_factor;
		} elseif ($year < -5499 || ($year == -5499 && $month < 8) || ($year == -5499 && $month == 8 && $day < 18)) {
			$j = 0;
		} else {
			if ($year < 0) {
				$hy = $year + 5520;
			} else {
				$hy = $year + 5519;
			}
			$n = intval($hy / 30);
			$j = ($n * 10631) + round(($hy - ($n * 30)) * 354.36667);
			$hm = $month - 1;
			$j = $j + round($hm * 29.5) + $day;
			$j -= 7666;
		}
		return $j;
	}

	/**
	 * Returns count of days in month (29 or 30)
	 *
	 * @param int $month
	 *        	the Hijri month
	 * @param int $year
	 *        	the Hijri year
	 * @param bool $umalqura
	 *        	TRUE to use Um Al-Qura, FALSE to use Tabular, defaults from Calendar object
	 * @return int count of days in month 29 or 30
	 */
	public function days_in_month($month, $year, $umalqura = null)
	{
		if (!isset($umalqura)) {
			$umalqura = $this->umalqura;
		}
		if ($umalqura && $year >= self::umstartyear && $year <= self::umendyear) {
			$i = $this->month2off($month, $year);
			if (empty($this->umdata)) {
				$this->get_umalquradata();
			}
			$t = $this->umdata[$i + 1] - $this->umdata[$i];
		} else {
			if ($month == 12) {
				if ($year < 0) {
					$year = $year + 5521;
				}
				if (round(($year % 30) * 0.36667) > round((($year - 1) % 30) * 0.36667)) {
					$t = 30;
				} else {
					$t = 29;
				}
			} else {
				$t = 29 + ($month % 2);
			}
		}
		return $t;
	}

	/**
	 * Returns array of the hijri month names in 20 languages
	 *
	 * @param string $langcode
	 *        	the language code
	 * @return array Array of Hijri month names, keys are month number (start from 1), values are string of month name
	 */
	public function monthnames($langcode = 'ar')
	{
		switch ($langcode) {
			case 'ar':
				$months = array(1 => 'محرم', 'صفر', 'ربيع الأول', 'ربيع الثاني', 'جمادى الأولى', 'جمادى الآخرة', 'رجب', 'شعبان', 'رمضان', 'شوال', 'ذو القعدة', 'ذو الحجة');
				break;
			case 'fa':
				$months = array(1 => 'محرم', 'صفر ', 'ربیع‌الاول', 'ربیع‌الثانی', 'جمادی‌الاول', 'جمادی‌الثانی', 'رجب', 'شعبان', 'رمضان', 'شوال', 'ذیقعده', 'ذیحجه');
				break;
			case 'ur':
				$months = array(1 => 'محرم', 'صفر', 'ربیع الاول', 'ربیع الثانی', 'جمادی الاول', 'جمادی الثانی', 'رجب', 'شعبان', 'رمضان', 'شوال', 'ذوالقعدہ', 'ذوالحجہ');
				break;
			case 'sq':
				$months = array(1 => 'Muharrem', 'Sefer', 'Rebi el-Evvel', 'Rebi uth-Thani', 'Xhumade Ula', 'Xhumade el-Ahir', 'Rexheb', 'Sha’ban', 'Ramazani', 'Sheval', 'Dhulkade', 'Dhulhixhxhe');
				break;
			case 'az':
				$months = array(1 => 'Məhərrəmc', 'Səfər', 'Rəbiüləvvəl', 'Rəbiülaxır', 'Cəmadiyələvvəl', 'Cəmadiyəlaxır', 'Rəcəb', 'Şaban', 'Ramazan', 'Şəvval', 'Zilqədə', 'Zilhiccə');
				break;
			case 'bn':
				$months = array(1 => 'মহররম', 'সফর', 'রবিউল আউয়াল', 'রবিউস সানি', 'জমাদিউল আউয়াল', 'জমাদিউস সানি', 'রজব', 'শা‘বান', 'রমজান', 'শাওয়াল', 'জ্বিলকদ', 'জ্বিলহজ্জ');
				break;
			case 'bs':
				$months = array(1 => 'Muharrem', 'Safer', 'Rebiul-evvel', 'Rebiul-ahir', 'Džumade-l-ula', 'Džumade-l-ahira', 'Redžeb', 'Ša\'ban', 'Ramazan', 'Ševval', 'Zu-l-ka\'de', 'Zu-l-hidždže');
				break;
			case 'zh':
				$months = array(1 => '穆哈兰姆月', '色法尔月', '赖比尔·敖外鲁月', '赖比尔·阿色尼月', '主马达·敖外鲁月', '主马达·阿色尼月', '赖哲卜月', '舍尔邦月', '赖买丹月', '闪瓦鲁月', '都尔喀尔德月', '都尔黑哲月');
				break;
			case 'fr':
				$months = array(1 => 'Mouharram', 'Safar', 'Rabia al awal', 'Rabia ath-thani', 'Joumada al oula', 'Joumada ath-thania', 'Rajab', 'Chaabane', 'Ramadan', 'Chawwal', 'Dhou al qi`da', 'Dhou al-hijja');
				break;
			case 'de':
				$months = array(1 => 'Muharram', 'Safar', 'Rabiʿ al-auwal', 'Rabiʿ ath-thani', 'Dschumada l-ula', 'Dschumada th-thaniyya', 'Radschab', 'Schaʿban', 'Ramadan', 'Schawwal', 'Dhu l-qaʿda', 'Dhu l-hiddscha');
				break;
			case 'hi':
				$months = array(1 => 'मुहर्रम', 'सफ़र', 'रबीउल अव्वल', 'रबीउल आख़िर', 'जमादी-उल-अव्वल', 'जमादी-उल-आख़िर', 'रजब', 'शाबान', 'रमज़ान', 'शव्वाल', 'ज़िलक़ाद', 'ज़िलहिज्ज');
				break;
			case 'id':
				$months = array(1 => 'Muharram', 'Safar', 'Rabiul awal', 'Rabiul akhir', 'Jumadil awal', 'Jumadil akhir', 'Rajab', 'Sya\'ban', 'Ramadhan', 'Syawal', 'Dzulkaidah', 'Dzulhijjah');
				break;
			case 'ckb':
				$months = array(1 => 'موحەڕڕەم', 'سەفەر', 'ڕەبیعەلئەووەل', 'ڕەبیعەلئاخیر', 'جومادەلئوولا', 'جەمادەلئاخیر', 'ڕەجەب', 'شەعبان', 'ڕەمەزان', 'شەووال', 'زولقەعدە', 'زولحەججە');
				break;
			case 'ms':
				$months = array(1 => 'Muharam', 'Safar', 'Rabiulawal', 'Rabiulakhir', 'Jamadilawal', 'Jamadilakhir', 'Rejab', 'Syaaban', 'Ramadan', 'Syawal', 'Zulkaedah', 'Zulhijah');
				break;
			case 'ru':
				$months = array(1 => 'Мухаррам', 'Сафар', 'Раби-уль-авваль', 'Раби-уль-ахир', 'Джумад-уль-авваль', 'Джумад-уль-ахир', 'Раджаб', 'Шаабан', 'Рамадан', 'Шавваль', 'Зуль-Каада', 'Зуль-Хиджжа');
				break;
			case 'es':
				$months = array(1 => 'Muharram', 'Safar', 'Rabi\' al-Awwal', 'Rabi\' al-Thani', 'Yumada al-Wula', 'Yumada al-Thania', 'Rayab', 'Sha\'abán', 'Ramadán', 'Shawwal', 'Du al-Qa\'da', 'Du al-Hiyya');
				break;
			case 'tr':
				$months = array(1 => 'Muharrem', 'Safer', 'Rebiülevvel', 'Rebiülahir', 'Cemaziyelevvel', 'Cemaziyelahir', 'Recep', 'Şaban', 'Ramazan', 'Şevval', 'Zilkade', 'Zilhicce');
				break;
			case 'pt':
				$months = array(1 => 'Muharram', 'Safar', 'Rabi al-Awwal', 'Raby al-THaany', 'Jumaada al-Awal', 'Jumaada al-THaany', 'Rajab', 'Sha\'aban', 'Ramadan', 'Shawwal', 'Dhu al-Qidah', 'Dhu al-Hija');
				break;
			case 'it':
				$months = array(1 => 'Muhàrram', 'Sàfar', 'Rabì‘ al-àwwal', 'Rabì‘ ath-thàni', 'Jumàda al-àwwal', 'Jumàda al-akhìra', 'Ràjab', 'Sha‘bàn', 'Ramadàn', 'Shawwàl', 'Dhu l-qà‘da', 'Dhu l-hìjja');
				break;
			default:
				$months = array(1 => 'Muharram', 'Safar', 'Rabi Al Awwal', 'Rabi Al Thani', 'Jumada Al Oula', 'Jumada Al Akhira', 'Rajab', 'Shaban', 'Ramadan', 'Shawwal', 'Dhul Qidah', 'Dhul Hijjah');
				break;
		}
		return $months;
	}

	/**
	 * Returns the hijri month name in 20 languages
	 *
	 * @since 2.2.0 $short param added
	 * @param string $month
	 *        	the month number
	 * @param string $langcode
	 *        	the language ISO code
	 * @param bool $short
	 *        	return short names of months for European languages
	 * @return string Hijri month name
	 */
	public function month_name($month, $langcode = 'ar', $short = FALSE)
	{
		if ($short && in_array($langcode, array('en', 'fr', 'de', 'es', 'pt', 'it', 'en'))) {
			$hsmonths = array(1 => 'Muh', 'Saf', 'Rb1', 'Rb2', 'Jm1', 'Jm2', 'Raj', 'Sha', 'Ram', 'Shw', 'Qid', 'Hij');
			return $hsmonths[$month];
		} else {
			$mymonth_names = $this->monthnames($langcode);
			return $mymonth_names[$month];
		}
	}

	/**
	 * Return 1 if the given year is leap, 0 else
	 *
	 * @param int $year
	 *        	the Hijri Year
	 * @param bool $umalqura
	 *        	TRUE to use Um Al-Qura, FALSE to use Tabular, defaults from Calendar object
	 * @return int 1 if the given year is leap(have 355 days), 0 else
	 */
	public function leap_year($year, $umalqura = null)
	{
		if (!isset($umalqura)) {
			$umalqura = $this->umalqura;
		}
		if ($umalqura && $year >= self::umstartyear && $year <= self::umendyear) {
			$ii = (int) ($year - self::umstartyear) / 12;
			$L = (($this->umdata[12 * ($ii + 1)] - $this->umdata[12 * $ii]) > 354) ? (1) : (0);
		} else {
			if ($year < 0) {
				$year = $year + 5521;
			}
			$L = (round(($year % 30) * 0.36667) > round((($year - 1) % 30) * 0.36667)) ? (1) : (0);
		}
		return $L;
	}

	/**
	 *
	 * @internal
	 *
	 */
	protected function month2off($month, $year)
	{
		$ii = $year - self::umstartyear;
		$i = $month - 1 + 12 * $ii;
		return $i;
	}

	/**
	 * Checks the given Hijri date, returns true is the date is correct
	 *
	 * @param int $year
	 *        	the Hijri year
	 * @param int $month
	 *        	the Hijri month
	 * @param int $day
	 *        	the Hijri day
	 * @return boolean TRUE if the given date is correct, FALSE else
	 */
	public function checkHijriDate($year, $month, $day)
	{
		if (!is_int($year) || !is_int($month) || !is_int($day)) {
			return FALSE;
		} elseif ($month < 1 || $month > 12 || $day < 1 || $day > 30 || $year == 0) {
			return FALSE;
		} elseif ($day > $this->days_in_month($month, $year)) {
			return FALSE;
		} else {
			return TRUE;
		}
	}
	/**
	 * returns english suffix for number
	 * @param int $number
	 * @return string
	 * @since 2.3.0
	 */
	public static function english_suffix($number)
	{
		if ($number >= 10 && $number <= 19) {
				
			return 'th';
		} else {
			switch ($number % 10)
			{
				case 1 :
					return 'st';
					break;
				case 2 :
					return 'nd';
					break;
				case 3 :
					return 'rd';
					break;
			}
		}
	
		return 'th';
	}
	
}