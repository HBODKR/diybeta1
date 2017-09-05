<?php
/** Occitan (occitan)
 *
 * To improve a translation please visit https://translatewiki.net
 *
 * @ingroup Language
 * @file
 *
 * @author Boulaur
 * @author Cedric31
 * @author ChrisPtDe
 * @author Fryed-peach
 * @author Jfblanc
 * @author Kaganer
 * @author McDutchie
 * @author Nemo bis
 * @author Spacebirdy
 * @author Горан Анђелковић
 * @author לערי ריינהארט
 */

$bookstoreList = [
	'Amazon.fr' => 'http://www.amazon.fr/exec/obidos/ISBN=$1'
];

$namespaceNames = [
	NS_MEDIA            => 'Mèdia',
	NS_SPECIAL          => 'Especial',
	NS_TALK             => 'Discutir',
	NS_USER             => 'Utilizaire',
	NS_USER_TALK        => 'Discussion_Utilizaire',
	NS_PROJECT_TALK     => 'Discussion_$1',
	NS_FILE             => 'Fichièr',
	NS_FILE_TALK        => 'Discussion_Fichièr',
	NS_MEDIAWIKI        => 'MediaWiki',
	NS_MEDIAWIKI_TALK   => 'Discussion_MediaWiki',
	NS_TEMPLATE         => 'Modèl',
	NS_TEMPLATE_TALK    => 'Discussion_Modèl',
	NS_HELP             => 'Ajuda',
	NS_HELP_TALK        => 'Discussion_Ajuda',
	NS_CATEGORY         => 'Categoria',
	NS_CATEGORY_TALK    => 'Discussion_Categoria',
];

$namespaceAliases = [
	'Utilisator'            => NS_USER,
	'Discussion_Utilisator' => NS_USER_TALK,
	'Discutida_Utilisator' => NS_USER_TALK,
	'Discutida_Imatge'     => NS_FILE_TALK,
	'Mediaòiqui'           => NS_MEDIAWIKI,
	'Discussion_Mediaòiqui' => NS_MEDIAWIKI_TALK,
	'Discutida_Mediaòiqui' => NS_MEDIAWIKI_TALK,
	'Discutida_Modèl'      => NS_TEMPLATE_TALK,
	'Discutida_Ajuda'      => NS_HELP_TALK,
	'Discutida_Categoria'  => NS_CATEGORY_TALK,
	'Imatge'               => NS_FILE,
	'Discussion_Imatge'    => NS_FILE_TALK,
];

$specialPageAliases = [
	'Allmessages'               => [ 'Messatge_sistèma', 'Messatge_del_sistèma' ],
	'Allpages'                  => [ 'Totas_las_paginas' ],
	'Ancientpages'              => [ 'Paginas_ancianas' ],
	'Blankpage'                 => [ 'Pagina_blanca', 'PaginaBlanca' ],
	'Block'                     => [ 'Blocar', 'Blocatge' ],
	'Booksources'               => [ 'Obratge_de_referéncia', 'Obratges_de_referéncia' ],
	'BrokenRedirects'           => [ 'Redireccions_copadas', 'RedireccionsCopadas' ],
	'Categories'                => [ 'Categorias' ],
	'ChangePassword'            => [ 'Reïnicializacion_del_senhal', 'Reinicializaciondelsenhal' ],
	'Confirmemail'              => [ 'Confirmar_lo_corrièr_electronic', 'Confirmarlocorrièrelectronic', 'ConfirmarCorrièrElectronic' ],
	'Contributions'             => [ 'Contribucions' ],
	'CreateAccount'             => [ 'Crear_un_compte', 'CrearUnCompte', 'CrearCompte' ],
	'Deadendpages'              => [ 'Paginas_sul_camin_d\'enlòc' ],
	'DeletedContributions'      => [ 'Contribucions_escafadas', 'ContribucionsEscafadas' ],
	'DoubleRedirects'           => [ 'Redireccions_doblas', 'RedireccionsDoblas' ],
	'Emailuser'                 => [ 'Corrièr_electronic', 'Emèl', 'Emèil' ],
	'Export'                    => [ 'Exportar', 'Exportacion' ],
	'Fewestrevisions'           => [ 'Mens_de_revisions' ],
	'FileDuplicateSearch'       => [ 'Recèrca_fichièr_en_doble', 'RecèrcaFichièrEnDoble' ],
	'Filepath'                  => [ 'Camin_del_Fichièr', 'CamindelFichièr', 'CaminFichièr' ],
	'Import'                    => [ 'Impòrt', 'Importacion' ],
	'Invalidateemail'           => [ 'Invalidar_Corrièr_electronic', 'InvalidarCorrièrElectronic' ],
	'BlockList'                 => [ 'Utilizaires_blocats' ],
	'LinkSearch'                => [ 'Recèrca_de_ligams', 'RecèrcaDeLigams' ],
	'Listadmins'                => [ 'Lista_dels_administrators', 'Listadelsadministrators', 'Lista_dels_admins', 'Listadelsadmins', 'Lista_admins', 'Listaadmins' ],
	'Listbots'                  => [ 'Lista_dels_Bòts', 'ListadelsBòts', 'Lista_dels_Bots', 'ListadelsBots' ],
	'Listfiles'                 => [ 'Lista_dels_imatges', 'ListaDelsImatges' ],
	'Listgrouprights'           => [ 'Lista_dels_gropes_utilizaire', 'ListadelsGropesUtilizaire', 'ListaGropesUtilizaire', 'Tièra_dels_gropes_utilizaire', 'TièradelsGropesUtilizaire', 'TièraGropesUtilizaire' ],
	'Listredirects'             => [ 'Lista_de_las_redireccions', 'Listadelasredireccions', 'Lista_dels_redirects', 'Listadelsredirects', 'Lista_redireccions', 'Listaredireccions', 'Lista_redirects', 'Listaredirects' ],
	'Listusers'                 => [ 'Lista_dels_utilizaires', 'ListaDelsUtilizaires' ],
	'Lockdb'                    => [ 'Varrolhar_la_banca' ],
	'Log'                       => [ 'Jornal', 'Jornals' ],
	'Lonelypages'               => [ 'Paginas_orfanèlas' ],
	'Longpages'                 => [ 'Articles_longs' ],
	'MergeHistory'              => [ 'Fusionar_l\'istoric', 'Fusionarlistoric' ],
	'MIMEsearch'                => [ 'Recèrca_MIME' ],
	'Mostcategories'            => [ 'Mai_de_categorias' ],
	'Mostimages'                => [ 'Mai_d\'imatges' ],
	'Mostlinked'                => [ 'Imatges_mai_utilizats' ],
	'Mostlinkedcategories'      => [ 'Categorias_mai_utilizadas', 'CategoriasMaiUtilizadas' ],
	'Mostlinkedtemplates'       => [ 'Modèls_mai_utilizats', 'ModèlsMaiUtilizats' ],
	'Mostrevisions'             => [ 'Mai_de_revisions' ],
	'Movepage'                  => [ 'Tornar_nomenar', 'Cambiament_de_nom' ],
	'Mycontributions'           => [ 'Mas_contribucions', 'Mascontribucions' ],
	'Mypage'                    => [ 'Ma_pagina', 'Mapagina' ],
	'Mytalk'                    => [ 'Mas_discussions', 'Masdiscussions' ],
	'Newimages'                 => [ 'Imatges_novèls', 'ImatgesNovèls' ],
	'Newpages'                  => [ 'Paginas_novèlas' ],
	'Preferences'               => [ 'Preferéncias' ],
	'Prefixindex'               => [ 'Indèx' ],
	'Protectedpages'            => [ 'Paginas_protegidas' ],
	'Protectedtitles'           => [ 'Títols_protegits', 'TítolsProtegits' ],
	'Randompage'                => [ 'Pagina_a_l\'azard' ],
	'Randomredirect'            => [ 'Redireccion_a_l\'azard', 'Redirect_a_l\'azard' ],
	'Recentchanges'             => [ 'Darrièrs_cambiaments', 'DarrièrsCambiaments', 'Darrièras_Modificacions' ],
	'Recentchangeslinked'       => [ 'Seguit_dels_ligams' ],
	'Revisiondelete'            => [ 'Versions_suprimidas' ],
	'Search'                    => [ 'Recèrca', 'Recercar', 'Cercar' ],
	'Shortpages'                => [ 'Articles_brèus' ],
	'Specialpages'              => [ 'Paginas_especialas' ],
	'Statistics'                => [ 'Estatisticas', 'Stats' ],
	'Tags'                      => [ 'Balisas' ],
	'Uncategorizedcategories'   => [ 'Categorias_sens_categoria' ],
	'Uncategorizedimages'       => [ 'Imatges_sens_categoria' ],
	'Uncategorizedpages'        => [ 'Paginas_sens_categoria' ],
	'Uncategorizedtemplates'    => [ 'Modèls_sens_categoria' ],
	'Undelete'                  => [ 'Restablir', 'Restabliment' ],
	'Unlockdb'                  => [ 'Desvarrolhar_la_banca' ],
	'Unusedcategories'          => [ 'Categorias_inutilizadas' ],
	'Unusedimages'              => [ 'Imatges_inutilizats' ],
	'Unusedtemplates'           => [ 'Modèls_inutilizats', 'Modèlsinutilizats', 'Models_inutilizats', 'Modelsinutilizats', 'Modèls_pas_utilizats', 'Modèlspasutilizats', 'Models_pas_utilizats', 'Modelspasutilizats' ],
	'Unwatchedpages'            => [ 'Paginas_pas_seguidas' ],
	'Upload'                    => [ 'Telecargament', 'Telecargaments' ],
	'Userlogin'                 => [ 'Nom_d\'utilizaire' ],
	'Userlogout'                => [ 'Desconnexion' ],
	'Userrights'                => [ 'Dreches', 'Permission' ],
	'Wantedcategories'          => [ 'Categorias_demandadas' ],
	'Wantedfiles'               => [ 'Fichièrs_demandats', 'FichièrsDemandats' ],
	'Wantedpages'               => [ 'Paginas_demandadas' ],
	'Wantedtemplates'           => [ 'Modèls_demandats', 'ModèlsDemandats' ],
	'Watchlist'                 => [ 'Lista_de_seguit', 'ListraDeSeguit', 'Seguit', 'Lista_de_seguiment', 'ListraDeSeguiment', 'Seguiment' ],
	'Whatlinkshere'             => [ 'Paginas_ligadas' ],
	'Withoutinterwiki'          => [ 'Sens_interwiki', 'Sensinterwiki', 'Sens_interwikis', 'Sensinterwikis' ],
];

$magicWords = [
	'redirect'                  => [ '0', '#REDIRECCION', '#REDIRECT' ],
	'notoc'                     => [ '0', '__CAPDETAULA__', '__PASCAPDESOMARI__', '__PASCAPDETDM__', '__NOTOC__' ],
	'nogallery'                 => [ '0', '__CAPDEGALARIÁ__', '__CAPDEGALARIA__', '__PASCAPDEDEGALARIÁ__', '__NOGALLERY__' ],
	'forcetoc'                  => [ '0', '__FORÇARTAULA__', '__FORÇARSOMARI__', '__FORÇARTDM__', '__FORCETOC__' ],
	'toc'                       => [ '0', '__TAULA__', '__SOMARI__', '__TDM__', '__TOC__' ],
	'noeditsection'             => [ '0', '__SECCIONNONEDITABLA__', '__NOEDITSECTION__' ],
	'currentmonth'              => [ '1', 'MESCORRENT', 'MESACTUAL', 'CURRENTMONTH', 'CURRENTMONTH2' ],
	'currentmonthname'          => [ '1', 'NOMMESCORRENT', 'NOMMESACTUAL', 'CURRENTMONTHNAME' ],
	'currentmonthnamegen'       => [ '1', 'NOMGENMESCORRENT', 'NOMGENMESACTUAL', 'CURRENTMONTHNAMEGEN' ],
	'currentmonthabbrev'        => [ '1', 'ABREVMESCORRENT', 'ABREVMESACTUAL', 'CURRENTMONTHABBREV' ],
	'currentday'                => [ '1', 'JORNCORRENT', 'JORNACTUAL', 'CURRENTDAY' ],
	'currentday2'               => [ '1', 'JORNCORRENT2', 'JORNACTUAL2', 'CURRENTDAY2' ],
	'currentdayname'            => [ '1', 'NOMJORNCORRENT', 'NOMJORNACTUAL', 'CURRENTDAYNAME' ],
	'currentyear'               => [ '1', 'ANNADACORRENTA', 'ANNADAACTUALA', 'CURRENTYEAR' ],
	'currenttime'               => [ '1', 'DATACORRENTA', 'DATAACTUALA', 'CURRENTTIME' ],
	'currenthour'               => [ '1', 'ORACORRENTA', 'ORAACTUALA', 'CURRENTHOUR' ],
	'localmonth'                => [ '1', 'MESLOCAL', 'LOCALMONTH', 'LOCALMONTH2' ],
	'localmonthname'            => [ '1', 'NOMMESLOCAL', 'LOCALMONTHNAME' ],
	'localmonthnamegen'         => [ '1', 'NOMGENMESLOCAL', 'LOCALMONTHNAMEGEN' ],
	'localmonthabbrev'          => [ '1', 'ABREVMESLOCAL', 'LOCALMONTHABBREV' ],
	'localday'                  => [ '1', 'JORNLOCAL', 'LOCALDAY' ],
	'localday2'                 => [ '1', 'JORNLOCAL2', 'LOCALDAY2' ],
	'localdayname'              => [ '1', 'NOMJORNLOCAL', 'LOCALDAYNAME' ],
	'localyear'                 => [ '1', 'ANNADALOCALA', 'LOCALYEAR' ],
	'localtime'                 => [ '1', 'ORARILOCAL', 'LOCALTIME' ],
	'localhour'                 => [ '1', 'ORALOCALA', 'LOCALHOUR' ],
	'numberofpages'             => [ '1', 'NOMBREPAGINAS', 'NUMBEROFPAGES' ],
	'numberofarticles'          => [ '1', 'NOMBREARTICLES', 'NUMBEROFARTICLES' ],
	'numberoffiles'             => [ '1', 'NOMBREFICHIÈRS', 'NUMBEROFFILES' ],
	'numberofusers'             => [ '1', 'NOMBREUTILIZAIRES', 'NUMBEROFUSERS' ],
	'numberofactiveusers'       => [ '1', 'NOMBREUTILIZAIRESACTIUS', 'NUMBEROFACTIVEUSERS' ],
	'numberofedits'             => [ '1', 'NOMBREEDICIONS', 'NOMBREMODIFS', 'NUMBEROFEDITS' ],
	'pagename'                  => [ '1', 'NOMPAGINA', 'PAGENAME' ],
	'pagenamee'                 => [ '1', 'NOMPAGINAX', 'PAGENAMEE' ],
	'namespace'                 => [ '1', 'ESPACINOMENATGE', 'NAMESPACE' ],
	'namespacee'                => [ '1', 'ESPACINOMENATGEX', 'NAMESPACEE' ],
	'talkspace'                 => [ '1', 'ESPACIDISCUSSION', 'TALKSPACE' ],
	'talkspacee'                => [ '1', 'ESPACIDISCUSSIONX', 'TALKSPACEE' ],
	'subjectspace'              => [ '1', 'ESPACISUBJECTE', 'ESPACISUBJÈCTE', 'ESPACIARTICLE', 'SUBJECTSPACE', 'ARTICLESPACE' ],
	'subjectspacee'             => [ '1', 'ESPACISUBJECTEX', 'ESPACISUBJÈCTEX', 'ESPACIARTICLEX', 'SUBJECTSPACEE', 'ARTICLESPACEE' ],
	'fullpagename'              => [ '1', 'NOMPAGINACOMPLET', 'FULLPAGENAME' ],
	'fullpagenamee'             => [ '1', 'NOMPAGINACOMPLETX', 'FULLPAGENAMEE' ],
	'subpagename'               => [ '1', 'NOMSOSPAGINA', 'SUBPAGENAME' ],
	'subpagenamee'              => [ '1', 'NOMSOSPAGINAX', 'SUBPAGENAMEE' ],
	'basepagename'              => [ '1', 'NOMBASADEPAGINA', 'BASEPAGENAME' ],
	'basepagenamee'             => [ '1', 'NOMBASADEPAGINAX', 'BASEPAGENAMEE' ],
	'talkpagename'              => [ '1', 'NOMPAGINADISCUSSION', 'TALKPAGENAME' ],
	'talkpagenamee'             => [ '1', 'NOMPAGINADISCUSSIONX', 'TALKPAGENAMEE' ],
	'subjectpagename'           => [ '1', 'NOMPAGINASUBJECTE', 'NOMPAGINASUBJÈCTE', 'NOMPAGINAARTICLE', 'SUBJECTPAGENAME', 'ARTICLEPAGENAME' ],
	'subjectpagenamee'          => [ '1', 'NOMPAGINASUBJECTEX', 'NOMPAGINASUBJÈCTEX', 'NOMPAGINAARTICLEX', 'SUBJECTPAGENAMEE', 'ARTICLEPAGENAMEE' ],
	'img_thumbnail'             => [ '1', 'vinheta', 'thumb', 'thumbnail' ],
	'img_manualthumb'           => [ '1', 'vinheta=$1', 'thumbnail=$1', 'thumb=$1' ],
	'img_right'                 => [ '1', 'drecha', 'dreta', 'right' ],
	'img_left'                  => [ '1', 'esquèrra', 'senèstra', 'gaucha', 'left' ],
	'img_none'                  => [ '1', 'neant', 'nonrés', 'none' ],
	'img_center'                => [ '1', 'centrat', 'center', 'centre' ],
	'img_framed'                => [ '1', 'quadre', 'enquagrat', 'frame', 'framed', 'enframed' ],
	'img_frameless'             => [ '1', 'sens_quadre', 'frameless' ],
	'img_upright'               => [ '1', 'redreça', 'redreça$1', 'redreça $1', 'upright', 'upright=$1', 'upright $1' ],
	'img_border'                => [ '1', 'bordadura', 'border' ],
	'img_baseline'              => [ '1', 'linha_de_basa', 'baseline' ],
	'img_sub'                   => [ '1', 'indici', 'ind', 'sub' ],
	'img_super'                 => [ '1', 'exp', 'super', 'sup' ],
	'img_top'                   => [ '1', 'naut', 'top' ],
	'img_text_top'              => [ '1', 'naut-tèxte', 'naut-txt', 'text-top' ],
	'img_middle'                => [ '1', 'mitan', 'middle' ],
	'img_bottom'                => [ '1', 'bas', 'bottom' ],
	'img_text_bottom'           => [ '1', 'bas-tèxte', 'bas-txt', 'text-bottom' ],
	'img_link'                  => [ '1', 'ligam=$1', 'link=$1' ],
	'sitename'                  => [ '1', 'NOMSIT', 'NOMSITE_NOMSITI', 'SITENAME' ],
	'ns'                        => [ '0', 'ESPACEN:', 'NS:' ],
	'localurl'                  => [ '0', 'URLLOCALA:', 'LOCALURL:' ],
	'localurle'                 => [ '0', 'URLLOCALAX:', 'LOCALURLE:' ],
	'server'                    => [ '0', 'SERVIDOR', 'SERVER' ],
	'servername'                => [ '0', 'NOMSERVIDOR', 'SERVERNAME' ],
	'scriptpath'                => [ '0', 'CAMINESCRIPT', 'SCRIPTPATH' ],
	'grammar'                   => [ '0', 'GRAMATICA:', 'GRAMMAR:' ],
	'gender'                    => [ '0', 'GENRE:', 'GENDER:' ],
	'currentweek'               => [ '1', 'SETMANACORRENTA', 'CURRENTWEEK' ],
	'currentdow'                => [ '1', 'JDSCORRENT', 'CURRENTDOW' ],
	'localweek'                 => [ '1', 'SETMANALOCALA', 'LOCALWEEK' ],
	'localdow'                  => [ '1', 'JDSLOCAL', 'LOCALDOW' ],
	'revisionid'                => [ '1', 'NUMÈROVERSION', 'REVISIONID' ],
	'revisionday'               => [ '1', 'DATAVERSION', 'REVISIONDAY' ],
	'revisionday2'              => [ '1', 'DATAVERSION2', 'REVISIONDAY2' ],
	'revisionmonth'             => [ '1', 'MESREVISION', 'REVISIONMONTH' ],
	'revisionyear'              => [ '1', 'ANNADAREVISION', 'ANREVISION', 'REVISIONYEAR' ],
	'revisiontimestamp'         => [ '1', 'ORAREVISION', 'REVISIONTIMESTAMP' ],
	'fullurl'                   => [ '0', 'URLCOMPLETA:', 'FULLURL:' ],
	'fullurle'                  => [ '0', 'URLCOMPLETAX:', 'FULLURLE:' ],
	'lcfirst'                   => [ '0', 'INITMINUS:', 'LCFIRST:' ],
	'ucfirst'                   => [ '0', 'INITMAJUS:', 'UCFIRST:' ],
	'lc'                        => [ '0', 'MINUS:', 'LC:' ],
	'uc'                        => [ '0', 'MAJUS:', 'CAPIT:', 'UC:' ],
	'raw'                       => [ '0', 'LINHA:', 'BRUT:', 'RAW:' ],
	'displaytitle'              => [ '1', 'AFICHARTÍTOL', 'DISPLAYTITLE' ],
	'rawsuffix'                 => [ '1', 'BRUT', 'B', 'R' ],
	'newsectionlink'            => [ '1', '__LIGAMSECCIONNOVÈLA__', '__NEWSECTIONLINK__' ],
	'nonewsectionlink'          => [ '1', '__PASCAPDELIGAMSECCIONNOVÈLA__', '__NONEWSECTIONLINK__' ],
	'currentversion'            => [ '1', 'VERSIONACTUALA', 'CURRENTVERSION' ],
	'urlencode'                 => [ '0', 'ENCÒDAURL:', 'URLENCODE:' ],
	'anchorencode'              => [ '0', 'ENCÒDAANCÒRA', 'ANCHORENCODE' ],
	'currenttimestamp'          => [ '1', 'INSTANTACTUAL', 'CURRENTTIMESTAMP' ],
	'localtimestamp'            => [ '1', 'INSTANTLOCAL', 'LOCALTIMESTAMP' ],
	'directionmark'             => [ '1', 'MARCADIRECCION', 'MARCADIR', 'DIRECTIONMARK', 'DIRMARK' ],
	'language'                  => [ '0', '#LENGA:', '#LANGUAGE:' ],
	'contentlanguage'           => [ '1', 'LENGACONTENGUT', 'LENGCONTENGUT', 'CONTENTLANGUAGE', 'CONTENTLANG' ],
	'pagesinnamespace'          => [ '1', 'PAGINASDINSESPACI:', 'PAGESINNAMESPACE:', 'PAGESINNS:' ],
	'numberofadmins'            => [ '1', 'NOMBREADMINS', 'NUMBEROFADMINS' ],
	'formatnum'                 => [ '0', 'FORMATNOMBRE', 'FORMATNUM' ],
	'padleft'                   => [ '0', 'BORRATGEESQUÈRRA', 'PADLEFT' ],
	'padright'                  => [ '0', 'BORRATGEDRECHA', 'PADRIGHT' ],
	'special'                   => [ '0', 'especial', 'special' ],
	'defaultsort'               => [ '1', 'ORDENA:', 'CLAUDETRIADA:', 'DEFAULTSORT:', 'DEFAULTSORTKEY:', 'DEFAULTCATEGORYSORT:' ],
	'filepath'                  => [ '0', 'CAMIN:', 'FILEPATH:' ],
	'tag'                       => [ '0', 'balisa', 'tag' ],
	'hiddencat'                 => [ '1', '__CATAMAGADA__', '__HIDDENCAT__' ],
	'pagesincategory'           => [ '1', 'PAGINASDINSCAT', 'PAGESINCATEGORY', 'PAGESINCAT' ],
	'pagesize'                  => [ '1', 'TALHAPAGINA', 'PAGESIZE' ],
	'noindex'                   => [ '1', '__PASCAPDINDÈX__', '__NOINDEX__' ],
	'staticredirect'            => [ '1', '__REDIRECCIONESTATICA__', '__STATICREDIRECT__' ],
	'protectionlevel'           => [ '1', 'NIVÈLDEPROTECCION', 'PROTECTIONLEVEL' ],
];

$datePreferences = [
	'default',
	'oc normal',
	'ISO 8601',
];

$defaultDateFormat = 'oc normal';

$dateFormats = [
	'oc normal time' => 'H.i',
	'oc normal date' => 'j F "de" Y',
	'oc normal both' => 'j F "de" Y "a" H.i',
];

$separatorTransformTable = [ ',' => "\xc2\xa0", '.' => ',' ];

$linkTrail = "/^([a-zàâçéèêîôû]+)(.*)$/sDu";

