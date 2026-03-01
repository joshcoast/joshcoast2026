import { computed } from 'vue'
import type { MenuItem } from '@/views/_components/menu/IvyMenuAccordion.vue'
import { useLabels } from '@/composables/useLabels'

export const templateCategories = computed<MenuItem[]>(() => {
  const { getLabel } = useLabels()

  return [
    {
      index: 'all',
      label: getLabel('all_templates'),
    },
    {
      index: 'favourites',
      label: getLabel('favourites'),
    },
    {
      index: 'order-forms',
      label: getLabel('order_forms'),
      subItems: [
        {
          index: 'product-order-forms',
          label: getLabel('product_order_forms'),
        },
        {
          index: 'tshirt-order-forms',
          label: getLabel('t_shirt_order_forms'),
        },
        {
          index: 'cake-order-forms',
          label: getLabel('cake_order_forms'),
        },
        {
          index: 'change-order-forms',
          label: getLabel('change_order_forms'),
        },
        {
          index: 'work-order-forms',
          label: getLabel('work_order_forms'),
        },
      ],
    },
    {
      index: 'contact-forms',
      label: getLabel('contact_forms'),
      subItems: [
        {
          index: 'emergency-contact-forms',
          label: getLabel('emergency_contact_forms'),
        },
      ],
    },
    /*{
      index: 'payment-forms',
      label: getLabel('payment_forms'),
      subItems: [
        {
          index: 'invoice-forms',
          label: getLabel('invoice_forms'),
        },
        {
          index: 'ach-payment-forms',
          label: getLabel('ach_payment_forms'),
        },
        {
          index: 'credit-card-payment-forms',
          label: getLabel('credit_card_payment_forms'),
        },
        {
          index: 'payment-request-forms',
          label: getLabel('payment_request_forms'),
        },
      ],
    },*/
    /*{
      index: 'sponsorship-forms',
      label: getLabel('sponsorship_forms'),
      subItems: [
        {
          index: 'event-sponsorship-forms',
          label: getLabel('event_sponsorship_forms'),
        },
      ],
    },*/
    {
      index: 'event-registration-forms',
      label: getLabel('event_registration_forms'),
      subItems: [
        {
          index: 'webinar-registration-forms',
          label: getLabel('webinar_registration_forms'),
        },
        {
          index: 'conference-registration-forms',
          label: getLabel('conference_registration_forms'),
        },
        {
          index: 'wedding-forms',
          label: getLabel('wedding_forms'),
        },
        {
          index: 'charity-event-forms',
          label: getLabel('charity_event_forms'),
        },
      ],
    },
    {
      index: 'booking-forms',
      label: getLabel('booking_forms'),
      subItems: [
        {
          index: 'appointment-booking-forms',
          label: getLabel('appointment_booking_forms'),
        },
        {
          index: 'reservation-forms',
          label: getLabel('reservation_forms'),
        },
      ],
    },
    {
      index: 'waiver-forms',
      label: getLabel('waiver_forms'),
      subItems: [
        {
          index: 'liability-waiver-forms',
          label: getLabel('liability_waiver_forms'),
        },
        {
          index: 'participation-waiver-forms',
          label: getLabel('participation_waiver_forms'),
        },
      ],
    },
    {
      index: 'registration-forms',
      label: getLabel('registration_forms'),
      subItems: [
        /*{
          index: 'vendor-registration-forms',
          label: getLabel('vendor_registration_forms'),
        },*/
        {
          index: 'workshop-registration-forms',
          label: getLabel('workshop_registration_forms'),
        },
      ],
    },
    {
      index: 'request-forms',
      label: getLabel('request_forms'),
      subItems: [
        /*{
          index: 'time-off-request-forms',
          label: getLabel('time_off_request_forms'),
        },*/
        /*{
          index: 'maintenance-request-forms',
          label: getLabel('maintenance_request_forms'),
        },*/
        /*{
          index: 'travel-request-forms',
          label: getLabel('travel_request_forms'),
        },
        {
          index: 'purchase-request-forms',
          label: getLabel('purchase_request_forms'),
        },
        {
          index: 'change-request-forms',
          label: getLabel('change_request_forms'),
        },
        {
          index: 'medical-records-request-forms',
          label: getLabel('medical_records_request_forms'),
        },*/
        {
          index: 'leave-request-forms',
          label: getLabel('leave_request_forms'),
        },
      ],
    },
    /*{
      index: 'application-forms',
      label: getLabel('application_forms'),
      subItems: [
        {
          index: 'job-application-forms',
          label: getLabel('job_application_forms'),
        },
        {
          index: 'employment-application-forms',
          label: getLabel('employment_application_forms'),
        },
        {
          index: 'tenant-application-forms',
          label: getLabel('tenant_application_forms'),
        },
        {
          index: 'vendor-application-forms',
          label: getLabel('vendor_application_forms'),
        },
      ],
    },*/
    /*{
      index: 'membership-forms',
      label: getLabel('membership_forms'),
      subItems: [],
    },*/
    {
      index: 'consent-forms',
      label: getLabel('consent_forms'),
      subItems: [
        /*{
          index: 'informed-consent-forms',
          label: getLabel('informed_consent_forms'),
        },*/
        {
          index: 'medical-consent-forms',
          label: getLabel('medical_consent_forms'),
        },
        {
          index: 'photo-consent-forms',
          label: getLabel('photo_consent_forms'),
        },
      ],
    },
    /*{
      index: 'release-forms',
      label: getLabel('release_forms'),
      subItems: [
        {
          index: 'photo-release-forms',
          label: getLabel('photo_release_forms'),
        },
        {
          index: 'medical-release-forms',
          label: getLabel('medical_release_forms'),
        },
        {
          index: 'model-release-forms',
          label: getLabel('model_release_forms'),
        },
        {
          index: 'media-release-forms',
          label: getLabel('media_release_forms'),
        },
        {
          index: 'liability-release-forms',
          label: getLabel('liability_release_forms'),
        },
        {
          index: 'video-release-forms',
          label: getLabel('video_release_forms'),
        },
        {
          index: 'talent-release-forms',
          label: getLabel('talent_release_forms'),
        },
        {
          index: 'social-media-release-forms',
          label: getLabel('social_media_release_forms'),
        },
        {
          index: 'information-release-forms',
          label: getLabel('information_release_forms'),
        },
      ],
    },*/
    /*{
      index: 'feedback-forms',
      label: getLabel('feedback_forms'),
      subItems: [
        {
          index: 'customer-feedback-forms',
          label: getLabel('customer_feedback_forms'),
        },
        {
          index: 'event-feedback-forms',
          label: getLabel('event_feedback_forms'),
        },
        {
          index: 'client-feedback-forms',
          label: getLabel('client_feedback_forms'),
        },
        {
          index: 'training-feedback-forms',
          label: getLabel('training_feedback_forms'),
        },
        {
          index: 'interview-feedback-forms',
          label: getLabel('interview_feedback_forms'),
        },
      ],
    },*/
    /*{
      index: 'survey-forms',
      label: getLabel('survey_forms'),
      subItems: [
        {
          index: 'nps-survey-forms',
          label: getLabel('nps_survey_forms'),
        },
        {
          index: 'satisfaction-survey-forms',
          label: getLabel('satisfaction_survey_forms'),
        },
      ],
    },*/
    /*{
      index: 'evaluation-forms',
      label: getLabel('evaluation_forms'),
      subItems: [
        {
          index: 'employee-evaluation-forms',
          label: getLabel('employee_evaluation_forms'),
        },
        {
          index: 'performance-evaluation-forms',
          label: getLabel('performance_evaluation_forms'),
        },
      ],
    },*/
    /*{
      index: 'intake-forms',
      label: getLabel('intake_forms'),
      subItems: [],
    },
    {
      index: 'referral-forms',
      label: getLabel('referral_forms'),
      subItems: [],
    },*/
    /*{
      index: 'donation-forms',
      label: getLabel('donation_forms'),
      subItems: [
        {
          index: 'church-donation-forms',
          label: getLabel('church_donation_forms'),
        },
        {
          index: 'fundraiser-donation-forms',
          label: getLabel('fundraiser_donation_forms'),
        },
      ],
    },*/
    /*{
      index: 'reimbursement-forms',
      label: getLabel('reimbursement_forms'),
      subItems: [
        {
          index: 'mileage-reimbursement-forms',
          label: getLabel('mileage_reimbursement_forms'),
        },
        {
          index: 'expenses-reimbursement-forms',
          label: getLabel('expenses_reimbursement_forms'),
        },
      ],
    },*/
    /*{
      index: 'agreement-forms',
      label: getLabel('agreement_forms'),
      subItems: [
        {
          index: 'rental-agreement-forms',
          label: getLabel('rental_agreement_forms'),
        },
        {
          index: 'partnership-agreement-forms',
          label: getLabel('partnership_agreement_forms'),
        },
      ],
    },*/
    /*{
      index: 'report-forms',
      label: getLabel('report_forms'),
      subItems: [
        {
          index: 'incident-report-forms',
          label: getLabel('incident_report_forms'),
        },
        {
          index: 'accident-report-forms',
          label: getLabel('accident_report_forms'),
        },
        {
          index: 'expenses-report-forms',
          label: getLabel('expenses_report_forms'),
        },
      ],
    },*/
    /*{
      index: 'sign-up-forms',
      label: getLabel('sign_up_forms'),
      subItems: [
        {
          index: 'newsletter-sign-up-forms',
          label: getLabel('newsletter_sign_up_forms'),
        },
        {
          index: 'waiting-list-sign-up-forms',
          label: getLabel('waiting_list_sign_up_forms'),
        },
        {
          index: 'beta-sign-up-forms',
          label: getLabel('beta_sign_up_forms'),
        },
      ],
    },*/
  ]
})
