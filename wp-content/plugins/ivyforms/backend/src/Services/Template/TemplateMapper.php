<?php

declare(strict_types=1);

namespace IvyForms\Services\Template;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Class TemplateMapper
 *
 * Maps template IDs to their corresponding template classes
 *
 * @package IvyForms\Services\Template
 */
class TemplateMapper
{
    /**
     * Template ID to class mapping
     *
     * @var array<string, string>
     */
    private static array $templateMap = [
        'contact_form' => ContactFormTemplate::class,
        'blank_form' => BlankFormTemplate::class,
        'wedding_rsvp' => WeddingRsvpTemplate::class,
        'anniversary_dinner_reservation_form' => AnniversaryDinnerReservationFormTemplate::class,
        'birthday_dinner_reservation_form' => BirthdayDinnerReservationFormTemplate::class,
        'private_dining_reservation_form' => PrivateDiningReservationFormTemplate::class,
        'craft_vendor_registration_form' => CraftVendorRegistrationFormTemplate::class,
        'food_vendor_registration_form' => FoodVendorRegistrationFormTemplate::class,
        'fitness_class_registration_form' => FitnessClassRegistrationFormTemplate::class,
        'yoga_class_registration_form' => YogaClassRegistrationFormTemplate::class,
        'cooking_class_registration_form' => CookingClassRegistrationFormTemplate::class,
        'conference_room_booking_form' => ConferenceRoomBookingFormTemplate::class,
        'product_order_form' => ProductOrderFormTemplate::class,
        'event_registration' => EventRegistrationTemplate::class,
        'event_tshirt_order_form' => EventTShirtOrderFormTemplate::class,
        'custom_tshirt_order_form' => CustomTShirtOrderFormTemplate::class,
        'custom_cake_order_form' => CustomCakeOrderFormTemplate::class,
        'wedding_cake_order_form' => WeddingCakeOrderFormTemplate::class,
        'birthday_cake_order_form' => BirthdayCakeOrderFormTemplate::class,
        'doctor_appointment_booking_form' => DoctorAppointmentBookingFormTemplate::class,
        'dinner_reservation_form' => DinnerReservationFormTemplate::class,
        'hair_salon_appointment_form' => HairSalonAppointmentFormTemplate::class,
        'nail_salon_appointment_form' => NailSalonAppointmentFormTemplate::class,
        'spa_appointment_booking_form' => SpaAppointmentBookingFormTemplate::class,
        'consultation_appointment_form' => ConsultationAppointmentFormTemplate::class,
        'birthday_tshirt_order_form' => BirthdayTShirtOrderFormTemplate::class,
        'corporate_tshirt_order_form' => CorporateTShirtOrderFormTemplate::class,
        'residential_construction_change_form' => ResidentialConstructionChangeFormTemplate::class,
        'festival_waiver_form' => FestivalWaiverFormTemplate::class,
        'charity_run_waiver_form' => CharityRunWaiverFormTemplate::class,
        'volunteer_event_waiver_form' => VolunteerEventWaiverFormTemplate::class,
        'community_event_waiver_form' => CommunityEventWaiverFormTemplate::class,
        'school_trip_waiver_form' => SchoolTripWaiverFormTemplate::class,
        'yoga_class_waiver_form' => YogaClassWaiverFormTemplate::class,
        'dance_class_waiver_form' => DanceClassWaiverFormTemplate::class,
        'hiking_trip_waiver_form' => HikingTripWaiverFormTemplate::class,
        'gym_waiver_form' => GymWaiverFormTemplate::class,
        'sports_event_waiver_form' => SportsEventWaiverFormTemplate::class,
        'universal_construction_change_form' => UniversalConstructionChangeFormTemplate::class,
        'maintenance_work_order_form' => MaintenanceWorkOrderFormTemplate::class,
        'vacation_leave_request_form' => VacationLeaveRequestFormTemplate::class,
        'sick_leave_request_form' => SickLeaveRequestFormTemplate::class,
        'annual_leave_request_form' => AnnualLeaveRequestFormTemplate::class,
        'maternity_leave_request_form' => MaternityLeaveRequestFormTemplate::class,
        'conference_registration_form' => ConferenceRegistrationFormTemplate::class,
        'webinar_registration_form' => WebinarRegistrationFormTemplate::class,
        'corporate_photo_consent_form' => CorporatePhotoConsentFormTemplate::class,
        'conference_photo_consent_form' => ConferencePhotoConsentFormTemplate::class,
        'sports_team_photo_consent_form' => SportsTeamPhotoConsentFormTemplate::class,
        'school_photo_consent_form' => SchoolPhotoConsentFormTemplate::class,
        'event_photo_consent_form' => EventPhotoConsentFormTemplate::class,
        'photo_video_consent_form' => PhotoVideoConsentFormTemplate::class,
        'workshop_registration_form' => WorkshopRegistrationFormTemplate::class,
        'charity_event_registration_form' => CharityEventRegistrationFormTemplate::class,
        'baby_shower_cake_order_form' => BabyShowerCakeOrderFormTemplate::class,
        'general_medical_consent_form' => GeneralMedicalConsentFormTemplate::class,
        'surgery_informed_consent_form' => SurgeryInformedConsentFormTemplate::class,
        'general_change_order_form' => GeneralChangeOrderFormTemplate::class,
        'marketing_conference_registration_form' => MarketingConferenceRegistrationFormTemplate::class,
        'medical_conference_registration_form' => MedicalConferenceRegistrationFormTemplate::class,
        'tech_conference_registration_form' => TechConferenceRegistrationFormTemplate::class,
        'blood_transfusion_consent_form' => BloodTransfusionConsentFormTemplate::class,
        'vaccination_consent_form' => VaccinationConsentFormTemplate::class,
        'anesthesia_consent_form' => AnesthesiaConsentFormTemplate::class,
        'garden_wedding_rsvp_form' => GardenWeddingRSVPFormTemplate::class,
        'beach_wedding_rsvp_form' => BeachWeddingRSVPFormTemplate::class,
        'destination_wedding_rsvp_form' => DestinationWeddingRSVPFormTemplate::class,
        'traditional_wedding_rsvp_form' => TraditionalWeddingRSVPFormTemplate::class,
        'wedding_registration_form' => WeddingRegistrationFormTemplate::class,
        'software_purchase_order_form' => SoftwarePurchaseOrderFormTemplate::class,
        'office_supplies_purchase_order_form' => OfficeSuppliesPurchaseOrderFormTemplate::class,
        'equipment_purchase_order_form' => EquipmentPurchaseOrderFormTemplate::class,
        'material_purchase_order_form' => MaterialPurchaseOrderFormTemplate::class,
        'education_conference_registration_form' => EducationConferenceRegistrationFormTemplate::class,
        'repair_work_order_form' => RepairWorkOrderFormTemplate::class,
        'installation_work_order_form' => InstallationWorkOrderFormTemplate::class,
        'team_tshirt_order_form' => TeamTshirtOrderFormTemplate::class,
        'kitchen_upgrade_change_form' => KitchenUpgradeChangeFormTemplate::class,
        'commercial_construction_change_form' => CommercialConstructionChangeFormTemplate::class,
    ];

    /**
     * Get the template map
     *
     * @return array<string, string>
     */
    public function getTemplateMap(): array
    {
        /**
         * Allows modification of the template mapper.
         *
         * @since 0.1.0
         *
         * @param array<string, string> $templateMaper The current template mapper.
         *
         * @return array<string, string> The modified template mapper.
         */
        self::$templateMap = apply_filters(
            'ivyforms/template/template_mapper',
            self::$templateMap
        );

        return self::$templateMap;
    }

    /**
     * Get template class by template ID
     *
     * @param string $templateId
     * @return string|null Template class name or null if not found
     */
    public static function getTemplateClass(string $templateId): ?string
    {
        return (new TemplateMapper())->getTemplateMap()[$templateId] ?? null;
    }

    /**
     * Get template data by template ID
     *
     * @param string $templateId
     * @return array<string, mixed>|null Template data or null if not found
     */
    public static function getTemplate(string $templateId): ?array
    {
        $templateClass = self::getTemplateClass($templateId);

        if (!$templateClass || !class_exists($templateClass)) {
            return null;
        }

        return $templateClass::getTemplate();
    }

    /**
     * Get all template IDs
     *
     * @return array<int, string>
     */
    public static function getAllTemplateIds(): array
    {
        return array_keys((new TemplateMapper())->getTemplateMap());
    }

    /**
     * Check if template ID exists
     *
     * @param string $templateId
     * @return bool
     */
    public static function hasTemplate(string $templateId): bool
    {
        return self::getTemplateClass($templateId) !== null;
    }

    /**
     * Get all templates as associative array
     *
     * @return array<string, array<string, mixed>>
     */
    public static function getAllTemplates(): array
    {
        $templates = [];

        foreach ((new TemplateMapper())->getTemplateMap() as $templateId => $templateClass) {
            // Skip BlankFormTemplate from public template list
            if ($templateClass === BlankFormTemplate::class) {
                continue;
            }

            if (class_exists($templateClass)) {
                $templateData = $templateClass::getTemplate();
                $templates[$templateId] = $templateData;
            }
        }

        return $templates;
    }
}
