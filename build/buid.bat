REM This will generate the zip package for Event Booking extension in /build/packages
REM This requires 7zip software to be installed
setlocal
SET PATH=%PATH%;C:\Program Files (x86)\7-Zip
rmdir /q /s packages
mkdir packages
REM Component
cd D:\www\eventbooking\com_eventbooking\
7z a -tzip D:\www\eventbooking\build/packages/com_eventbooking.zip *
REM Modules
cd D:\www\eventbooking\modules\mod_eb_cart\
7z a -tzip D:\www\eventbooking\build/packages/mod_eb_cart.zip *
cd D:\www\eventbooking\modules\mod_eb_categories\
7z a -tzip D:\www\eventbooking\build/packages/mod_eb_categories.zip *
cd D:\www\eventbooking\modules\mod_eb_events\
7z a -tzip D:\www\eventbooking\build/packages/mod_eb_events.zip *
cd D:\www\eventbooking\modules\mod_eb_locations\
7z a -tzip D:\www\eventbooking\build/packages/mod_eb_locations.zip *
cd D:\www\eventbooking\modules\mod_eb_minicalendar\
7z a -tzip D:\www\eventbooking\build/packages/mod_eb_minicalendar.zip *
cd D:\www\eventbooking\modules\mod_eb_search\
7z a -tzip D:\www\eventbooking\build/packages/mod_eb_search.zip *
cd D:\www\eventbooking\modules\mod_eb_googlemap\
7z a -tzip D:\www\eventbooking\build/packages/mod_eb_googlemap.zip *
cd D:\www\eventbooking\modules\mod_eb_view\
7z a -tzip D:\www\eventbooking\build/packages/mod_eb_view.zip *
cd D:\www\eventbooking\modules\mod_eb_cities\
7z a -tzip D:\www\eventbooking\build/packages/mod_eb_cities.zip *
cd D:\www\eventbooking\modules\mod_eb_states\
7z a -tzip D:\www\eventbooking\build/packages/mod_eb_states.zip *
REM Plugins
cd D:\www\eventbooking\plugins\content\ebevent\
7z a -tzip D:\www\eventbooking\build/packages/plug_content_ebevent.zip *
cd D:\www\eventbooking\plugins\content\ebregister\
7z a -tzip D:\www\eventbooking\build/packages/plug_content_ebregister.zip *
cd D:\www\eventbooking\plugins\content\ebcategory\
7z a -tzip D:\www\eventbooking\build/packages/plug_content_ebcategory.zip *
cd D:\www\eventbooking\plugins\content\ebrestriction\
7z a -tzip D:\www\eventbooking\build/packages/plug_content_ebrestriction.zip *
cd D:\www\eventbooking\plugins\editors-xtd\ebevent\
7z a -tzip D:\www\eventbooking\build/packages/plug_editors_xtd_ebevent.zip *
cd D:\www\eventbooking\plugins\editors-xtd\ebregister\
7z a -tzip D:\www\eventbooking\build/packages/plug_editors_xtd_ebregister.zip *
cd D:\www\eventbooking\plugins\eventbooking\acymailing\
7z a -tzip D:\www\eventbooking\build/packages/plug_eventbooking_acymailing.zip *
cd D:\www\eventbooking\plugins\eventbooking\cb\
7z a -tzip D:\www\eventbooking\build/packages/plug_eventbooking_cb.zip *
cd D:\www\eventbooking\plugins\eventbooking\invoice\
7z a -tzip D:\www\eventbooking\build/packages/plug_eventbooking_invoice.zip *
cd D:\www\eventbooking\plugins\eventbooking\jcomments\
7z a -tzip D:\www\eventbooking\build/packages/plug_eventbooking_jcomments.zip *
cd D:\www\eventbooking\plugins\eventbooking\joomlagroups\
7z a -tzip D:\www\eventbooking\build/packages/plug_eventbooking_joomlagroups.zip *
cd D:\www\eventbooking\plugins\eventbooking\jsactivities\
7z a -tzip D:\www\eventbooking\build/packages/plug_eventbooking_jsactivities.zip *
cd D:\www\eventbooking\plugins\eventbooking\mailchimp\
7z a -tzip D:\www\eventbooking\build/packages/plug_eventbooking_mailchimp.zip *
cd D:\www\eventbooking\plugins\eventbooking\map\
7z a -tzip D:\www\eventbooking\build/packages/plug_eventbooking_map.zip *
cd D:\www\eventbooking\plugins\eventbooking\moveregistrants\
7z a -tzip D:\www\eventbooking\build/packages/plug_eventbooking_moveregistrants.zip *
cd D:\www\eventbooking\plugins\eventbooking\unpublishevents\
7z a -tzip D:\www\eventbooking\build/packages/plug_eventbooking_unpublishevents.zip *
cd D:\www\eventbooking\plugins\eventbooking\userprofile\
7z a -tzip D:\www\eventbooking\build/packages/plug_eventbooking_userprofile.zip *
cd D:\www\eventbooking\plugins\eventbooking\easyprofile\
7z a -tzip D:\www\eventbooking\build/packages/plug_eventbooking_easyprofile.zip *
cd D:\www\eventbooking\plugins\eventbooking\contactenhanced\
7z a -tzip D:\www\eventbooking\build/packages/plug_eventbooking_contactenhanced.zip *
cd D:\www\eventbooking\plugins\eventbooking\easysocial\
7z a -tzip D:\www\eventbooking\build/packages/plug_eventbooking_easysocial.zip *
cd D:\www\eventbooking\plugins\eventbooking\jomsocial\
7z a -tzip D:\www\eventbooking\build/packages/plug_eventbooking_jomsocial.zip *
cd D:\www\eventbooking\plugins\eventbooking\jomsocial\
7z a -tzip D:\www\eventbooking\build/packages/plug_eventbooking_jomsocial.zip *
cd D:\www\eventbooking\plugins\eventbooking\membershippro\
7z a -tzip D:\www\eventbooking\build/packages/plug_eventbooking_membershippro.zip *
cd D:\www\eventbooking\plugins\eventbooking\field\
7z a -tzip D:\www\eventbooking\build/packages/plug_eventbooking_field.zip *
cd D:\www\eventbooking\plugins\eventbooking\dates\
7z a -tzip D:\www\eventbooking\build/packages/plug_eventbooking_dates.zip *
cd D:\www\eventbooking\plugins\eventbooking\tickettypes\
7z a -tzip D:\www\eventbooking\build/packages/plug_eventbooking_tickettypes.zip *
cd D:\www\eventbooking\plugins\eventbooking\system\
7z a -tzip D:\www\eventbooking\build/packages/plug_eventbooking_system.zip *
cd D:\www\eventbooking\plugins\finder\eventbooking\
7z a -tzip D:\www\eventbooking\build/packages/plug_finder_eventbooking.zip *
cd D:\www\eventbooking\plugins\search\eventbooking\
7z a -tzip D:\www\eventbooking\build/packages/plug_search_eventbooking.zip *
cd D:\www\eventbooking\plugins\system\ebreminder\
7z a -tzip D:\www\eventbooking\build/packages/plug_system_ebreminder.zip *
cd D:\www\eventbooking\plugins\system\ebdepositreminder\
7z a -tzip D:\www\eventbooking\build/packages/plug_system_ebdepositreminder.zip *
cd D:\www\eventbooking\plugins\community\eb_registrationhistory
7z a -tzip D:\www\eventbooking\build/packages/plug_js_regisrationhisory.zip *
cd D:\www\eventbooking\plugins\installer\eventbooking
7z a -tzip D:\www\eventbooking\build/packages/plug_installer_eventbooking.zip *
REM package
cd D:\www\eventbooking\build\
copy D:\www\eventbooking\pkg_eventbooking.xml
