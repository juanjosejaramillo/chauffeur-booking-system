<?php

namespace App\Filament\Resources\EmailTemplates\Pages;

use App\Filament\Resources\EmailTemplates\EmailTemplateResource;
use App\Models\EmailTemplate;
use App\Models\Booking;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\HtmlString;

class EmailTemplateEditor extends EditRecord implements HasForms
{
    use InteractsWithForms;
    
    protected static string $resource = EmailTemplateResource::class;
    protected string $view = 'filament.resources.email-templates.pages.email-template-editor';
    
    public $previewHtml = '';
    public $isMobilePreview = false;
    
    protected function getHeaderActions(): array
    {
        return [
            Action::make('preview')
                ->label('Preview Email')
                ->icon('heroicon-o-eye')
                ->color('info')
                ->action(fn () => $this->refreshPreview()),
                
            Action::make('sendTest')
                ->label('Send Test Email')
                ->icon('heroicon-o-paper-airplane')
                ->color('success')
                ->form([
                    \Filament\Forms\Components\TextInput::make('test_email')
                        ->label('Test Email Address')
                        ->email()
                        ->required()
                        ->default(auth()->user()->email ?? ''),
                ])
                ->action(function (array $data) {
                    $this->sendTestEmail($data['test_email']);
                }),
                
            Action::make('toggleMobile')
                ->label($this->isMobilePreview ? 'Desktop View' : 'Mobile View')
                ->icon($this->isMobilePreview ? 'heroicon-o-computer-desktop' : 'heroicon-o-device-phone-mobile')
                ->color('gray')
                ->action(function () {
                    $this->isMobilePreview = !$this->isMobilePreview;
                    $this->refreshPreview();
                }),
                
            DeleteAction::make(),
        ];
    }
    
    public function mount(int | string $record): void
    {
        parent::mount($record);
        $this->refreshPreview();
    }
    
    public function updated($propertyName)
    {
        parent::updated($propertyName);
        
        // Refresh preview when form data changes
        if (str_starts_with($propertyName, 'data.')) {
            $this->refreshPreview();
        }
    }
    
    public function refreshPreview()
    {
        try {
            $template = $this->record;
            
            // Get sample data for preview
            $sampleData = $this->getSampleData();
            
            // Save current form data temporarily to preview
            $tempTemplate = new EmailTemplate($this->form->getState());
            
            // Render the email with sample data
            $rendered = $tempTemplate->render($sampleData);
            
            // Wrap in email layout
            $this->previewHtml = view('emails.luxe-layout', [
                'content' => new HtmlString($rendered['body'])
            ])->render();
            
        } catch (\Exception $e) {
            $this->previewHtml = '<div class="p-4 text-red-600">Error rendering preview: ' . $e->getMessage() . '</div>';
        }
    }
    
    protected function getSampleData(): array
    {
        // Use a recent booking for sample data, or generate fake data
        $recentBooking = Booking::latest()->first();
        
        if ($recentBooking) {
            return [
                'booking_number' => $recentBooking->booking_number,
                'customer_name' => $recentBooking->customer_full_name,
                'customer_first_name' => $recentBooking->customer_first_name,
                'customer_last_name' => $recentBooking->customer_last_name,
                'customer_email' => $recentBooking->customer_email,
                'customer_phone' => $recentBooking->customer_phone,
                'pickup_address' => $recentBooking->pickup_address,
                'dropoff_address' => $recentBooking->dropoff_address,
                'pickup_date' => $recentBooking->pickup_date->format('F j, Y'),
                'pickup_time' => $recentBooking->pickup_date->format('g:i A'),
                'vehicle_type' => $recentBooking->vehicleType->display_name ?? 'Luxury Sedan',
                'estimated_fare' => number_format($recentBooking->estimated_fare, 2),
                'final_fare' => number_format($recentBooking->final_fare ?? $recentBooking->estimated_fare, 2),
                'special_instructions' => $recentBooking->special_instructions ?? 'None',
                'admin_notes' => $recentBooking->admin_notes ?? '',
                'company_name' => config('business.name', 'LuxRide'),
                'company_phone' => config('business.phone', '1-800-LUXRIDE'),
                'company_email' => config('business.email', 'support@luxride.com'),
                'support_url' => config('app.url') . '/support',
                'booking_url' => config('app.url') . '/booking/' . $recentBooking->booking_number,
            ];
        }
        
        // Fallback sample data
        return [
            'booking_number' => 'ABC123',
            'customer_name' => 'John Doe',
            'customer_first_name' => 'John',
            'customer_last_name' => 'Doe',
            'customer_email' => 'john.doe@example.com',
            'customer_phone' => '(555) 123-4567',
            'pickup_address' => '123 Main St, Miami, FL 33101',
            'dropoff_address' => 'Miami International Airport',
            'pickup_date' => 'December 25, 2025',
            'pickup_time' => '10:30 AM',
            'vehicle_type' => 'Luxury SUV',
            'estimated_fare' => '85.00',
            'final_fare' => '85.00',
            'special_instructions' => 'Please call upon arrival',
            'admin_notes' => '',
            'company_name' => config('business.name', 'LuxRide'),
            'company_phone' => config('business.phone', '1-800-LUXRIDE'),
            'company_email' => config('business.email', 'support@luxride.com'),
            'support_url' => config('app.url') . '/support',
            'booking_url' => config('app.url') . '/booking/ABC123',
        ];
    }
    
    protected function sendTestEmail(string $email)
    {
        try {
            $template = $this->record;
            $sampleData = $this->getSampleData();
            
            // Save current form state
            $this->save();
            
            // Render the email
            $rendered = $template->render($sampleData);
            
            // Send test email
            Mail::html(
                view('emails.luxe-layout', [
                    'content' => new HtmlString($rendered['body'])
                ])->render(),
                function ($message) use ($email, $rendered) {
                    $message->to($email)
                        ->subject('[TEST] ' . $rendered['subject']);
                }
            );
            
            Notification::make()
                ->title('Test Email Sent')
                ->body("Test email has been sent to {$email}")
                ->success()
                ->send();
                
        } catch (\Exception $e) {
            Notification::make()
                ->title('Failed to Send Test Email')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}