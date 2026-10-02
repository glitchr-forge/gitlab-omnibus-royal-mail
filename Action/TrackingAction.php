<?php

namespace Omnibus\RoyalMail\Action;

use Omnibus\Action\ActionInterface;
use Omnibus\Action\ApiAwareInterface;
use Omnibus\Action\ApiAwareTrait;
use Omnibus\Model\Tracking as TrackingModel;
use Omnibus\Model\TrackingEvent;
use Omnibus\Model\TrackingStatus;
use Omnibus\Request\Request;
use Omnibus\Request\Tracking;
use Omnibus\RoyalMail\Api;

/** The Tracking API's events for a mail piece, oldest first. */
final class TrackingAction implements ActionInterface, ApiAwareInterface
{
    /** @use ApiAwareTrait<Api> */
    use ApiAwareTrait;

    public function __construct()
    {
        $this->apiClass = Api::class;
    }

    public function supports(Request $request): bool
    {
        return $request instanceof Tracking;
    }

    public function execute(Request $request): void
    {
        \assert($request instanceof Tracking);
        $data = $this->api->track($request->trackingNumber);
        $piece = $data['mailPieces'] ?? $data;
        $events = [];
        foreach ($piece['events'] ?? [] as $event) {
            $events[] = new TrackingEvent(new \DateTimeImmutable((string) ($event['eventDateTime'] ?? 'now')), self::status($event['eventCode'] ?? null, $event['eventName'] ?? null), (string) ($event['eventName'] ?? ''), $event['locationName'] ?? null, $event['eventCode'] ?? null);
        }
        usort($events, static fn (TrackingEvent $a, TrackingEvent $b) => $a->at <=> $b->at);
        $status = self::status($piece['summary']['lastEventCode'] ?? null, $piece['summary']['statusDescription'] ?? ($events ? $events[array_key_last($events)]->description : null));
        $request->setResult(new TrackingModel('royal_mail', $request->trackingNumber, $status, $events));
    }

    private static function status(?string $code, ?string $name): TrackingStatus
    {
        $n = strtolower((string) $name);

        return match (true) {
            str_starts_with((string) $code, 'EVKSP') || str_contains($n, 'delivered') => TrackingStatus::DELIVERED,
            str_contains($n, 'out for delivery') || str_contains($n, 'due to be delivered') => TrackingStatus::OUT_FOR_DELIVERY,
            str_contains($n, 'ready for collection') || str_contains($n, 'available for collection') => TrackingStatus::AVAILABLE_FOR_PICKUP,
            str_contains($n, 'returned') || str_contains($n, 'return to sender') => TrackingStatus::RETURNED,
            str_contains($n, 'attempted') || str_contains($n, 'unable') || str_contains($n, 'delay') => TrackingStatus::EXCEPTION,
            str_contains($n, 'received') || str_contains($n, 'in transit') || str_contains($n, 'arrived') || str_contains($n, 'dispatched') || str_contains($n, 'accepted') => TrackingStatus::IN_TRANSIT,
            str_contains($n, 'advised') || str_contains($n, 'label') => TrackingStatus::PENDING,
            default => TrackingStatus::UNKNOWN,
        };
    }
}
