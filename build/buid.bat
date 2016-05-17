REM This will generate the zip package for Event Booking extension in /build/packages
REM This requires 7zip software to be installed
setlocal
SET PATH=%PATH%;C:\Program Files (x86)\7-Zip
rmdir /q /s packages
mkdir packages
REM Component
cd E:\www\eventbooking\com_eventbooking\
7z a -tzip E:\www\eventbooking\build/packages/com_eventbooking.zip *
REM Modules
cd E:\www\eventbooking\modules\mod_eb_cart\
7z a -tzip E:\www\eventbooking\build/packages/mod_eb_cart.zip *
cd E:\www\eventbooking\modules\mod_eb_categories\
7z a -tzip E:\www\eventbooking\build/packages/mod_eb_categories.zip *
cd E:\www\eventbooking\modules\mod_eb_events\
7z a -tzip E:\www\eventbooking\build/packages/mod_eb_events.zip *
cd E:\www\eventbooking\modules\mod_eb_locations\
7z a -tzip E:\www\eventbooking\build/packages/mod_eb_locations.zip *
cd E:\www\eventbooking\modules\mod_eb_minicalendar\
7z a -tzip E:\www\eventbooking\build/packages/mod_eb_minicalendar.zip *
cd E:\www\eventbooking\modules\mod_eb_search\
7z a -tzip E:\www\eventbooking\build/packages/mod_eb_search.zip *
cd E:\www\eventbooking\modules\mod_eb_googlemap\
7z a -tzip E:\www\eventbooking\build/packages/mod_eb_googlemap.zip *
cd E:\www\eventbooking\modules\mod_eb_view\
7z a -tzip E:\www\eventbooking\build/packages/mod_eb_view.zip *
REM Plugins
cd E:\www\eventbooking\plugins\content\ebevent\
7z a -tzip E:\www\eventbooking\build/packages/plug_content_ebevent.zip *
cd E:\www\eventbooking\plugins\content\ebregister\
7z a -tzip E:\www\eventbooking\build/packages/plug_content_ebregister.zip *
cd E:\www\eventbooking\plugins\content\ebcategory\
7z a -tzip E:\www\eventbooking\build/packages/plug_content_ebcategory.zip *
cd E:\www\eventbooking\plugins\content\ebrestriction\
7z a -tzip E:\www\eventbooking\build/packages/plug_content_ebrestriction.zip *
cd E:\www\eventbooking\plugins\editors-xtd\ebevent\
7z a -tzip E:\www\eventbooking\build/packages/plug_editors_xtd_ebevent.zip *
cd E:\www\eventbooking\plugins\editors-xtd\ebregister\
7z a -tzip E:\www\eventbooking\build/packages/plug_editors_xtd_ebregister.zip *
cd E:\www\eventbooking\plugins\eventbooking\acymailing\
7z a -tzip E:\www\eventbooking\build/packages/plug_eventbooking_acymailing.zip *
cd E:\www\eventbooking\plugins\eventbooking\cartupdate\
7z a -tzip E:\www\eventbooking\build/packages/plug_eventbooking_cartupdate.zip *
cd E:\www\eventbooking\plugins\eventbooking\cb\
7z a -tzip E:\www\eventbooking\build/packages/plug_eventbooking_cb.zip *
cd E:\www\eventbooking\plugins\eventbooking\invoice\
7z a -tzip E:\www\eventbooking\build/packages/plug_eventbooking_invoice.zip *
cd E:\www\eventbooking\plugins\eventbooking\jcomments\
7z a -tzip E:\www\eventbooking\build/packages/plug_eventbooking_jcomments.zip *
cd E:\www\eventbooking\plugins\eventbooking\joomlagroups\
7z a -tzip E:\www\eventbooking\build/packages/plug_eventbooking_joomlagroups.zip *
cd E:\www\eventbooking\plugins\eventbooking\jsactivities\
7z a -tzip E:\www\eventbooking\build/packages/plug_eventbooking_jsactivities.zip *
cd E:\www\eventbooking\plugins\eventbooking\mailchimp\
7z a -tzip E:\www\eventbooking\build/packages/plug_eventbooking_mailchimp.zip *
cd E:\www\eventbooking\plugins\eventbooking\map\
7z a -tzip E:\www\eventbooking\build/packages/plug_eventbooking_map.zip *
cd E:\www\eventbooking\plugins\eventbooking\moveregistrants\
7z a -tzip E:\www\eventbooking\build/packages/plug_eventbooking_moveregistrants.zip *
cd E:\www\eventbooking\plugins\eventbooking\unpublishevents\
7z a -tzip E:\www\eventbooking\build/packages/plug_eventbooking_unpublishevents.zip *
cd E:\www\eventbooking\plugins\eventbooking\userprofile\
7z a -tzip E:\www\eventbooking\build/packages/plug_eventbooking_userprofile.zip *
cd E:\www\eventbooking\plugins\eventbooking\easyprofile\
7z a -tzip E:\www\eventbooking\build/packages/plug_eventbooking_easyprofile.zip *
cd E:\www\eventbooking\plugins\eventbooking\contactenhanced\
7z a -tzip E:\www\eventbooking\build/packages/plug_eventbooking_contactenhanced.zip *
cd E:\www\eventbooking\plugins\eventbooking\easysocial\
7z a -tzip E:\www\eventbooking\build/packages/plug_eventbooking_easysocial.zip *
cd E:\www\eventbooking\plugins\eventbooking\jomsocial\
7z a -tzip E:\www\eventbooking\build/packages/plug_eventbooking_jomsocial.zip *
cd E:\www\eventbooking\plugins\eventbooking\jomsocial\
7z a -tzip E:\www\eventbooking\build/packages/plug_eventbooking_jomsocial.zip *
cd E:\www\eventbooking\plugins\eventbooking\membershippro\
7z a -tzip E:\www\eventbooking\build/packages/plug_eventbooking_membershippro.zip *
cd E:\www\eventbooking\plugins\eventbooking\field\
7z a -tzip E:\www\eventbooking\build/packages/plug_eventbooking_field.zip *
cd E:\www\eventbooking\plugins\search\eventbooking\
7z a -tzip E:\www\eventbooking\build/packages/plug_search_eventbooking.zip *
cd E:\www\eventbooking\plugins\system\ebreminder\
7z a -tzip E:\www\eventbooking\build/packages/plug_system_ebreminder.zip *
cd E:\www\eventbooking\plugins\system\ebdepositreminder\
7z a -tzip E:\www\eventbooking\build/packages/plug_system_ebdepositreminder.zip *
cd E:\www\eventbooking\plugins\community\eb_registrationhistory
7z a -tzip E:\www\eventbooking\build/packages/plug_js_regisrationhisory.zip *
REM package
cd E:\www\eventbooking\build\
copy E:\www\eventbooking\pkg_eventbooking.xml
