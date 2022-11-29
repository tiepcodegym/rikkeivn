<?php
$layout = \Rikkei\Core\Model\EmailQueue::getLayoutConfig(3);
?>
@extends($layout)

@section('css')
<style>
    p {
        margin: 8px auto;
    }
</style>
@endsection

@section('content')
    <div style="font-family:arial,sans-serif">
        <p>
            From
            @if (!empty($data['senderName']))
                <br>
                {{ $data['senderName'] }}
            @endif  
            <br>
            Rikkeisoft
        </p>
        
        <p>&nbsp;</p>
        
        <p dir="ltr"><samp><tt><span style="font-family:Arial,Helvetica,sans-serif;">Dear Sir, we would like to express our sincere gratitude to you for your continued patronage. We are looking forward to continuing working with you and making our collaboration relationship worthwhile. </span></tt></samp></p>
        
        <p dir="ltr"><samp><tt><span style="font-family:Arial,Helvetica,sans-serif;">Without your support, we would not be able to be as developed as today and celebrate the 10th anniversary of our establishment on April 6, 2022. Therefore, we want to take this opportunity to express our appreciation and invite you to our commemoration ceremony and celebration party, which will be held this July. </span></tt></samp></p>
        
        <p dir="ltr"><samp><tt><span style="font-family:Arial,Helvetica,sans-serif;">Please find the detailed information below.</span></tt></samp></p>
        
        <p><strong>Detailed information:</strong>&nbsp;</p>
        
        <p><samp><tt><span style="font-family:Arial,Helvetica,sans-serif;">&nbsp;</span></tt></samp></p>

        <p dir="ltr"><samp><tt><span style="font-family:Arial,Helvetica,sans-serif;">■Date and time: </span></tt></samp></p>
        
        <p dir="ltr"><samp><tt><span style="font-family:Arial,Helvetica,sans-serif;">【Commemoration Ceremony】July 17th（Sun）14: 00-16: 30</span></tt></samp></p>
        
        <p dir="ltr"><samp><tt><span style="font-family:Arial,Helvetica,sans-serif;">【Celebration Party】&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;July 17th（Sun）19: 00-22: 00</span></tt></samp></p>
        
        <p dir="ltr"><samp><tt><span style="font-family:Arial,Helvetica,sans-serif;">※Buses will be arranged for you to move from Hanoi to Halong Bay in the morning of Sunday, July 17th.</span></tt></samp></p>
        
        <p dir="ltr"><samp><tt><span style="font-family:Arial,Helvetica,sans-serif;">※Also, We will prepare the accommodation at the below mentioned hotel for the participants.</span></tt></samp></p>
        
        <p><samp><tt><span style="font-family:Arial,Helvetica,sans-serif;">&nbsp;</span></tt></samp></p>
        
        <p dir="ltr"><samp><tt><span style="font-family:Arial,Helvetica,sans-serif;">■Location: FLC Grand Hotel Halong</span></tt></samp></p>
        
        <p dir="ltr"><samp><tt><span style="font-family:Arial,Helvetica,sans-serif;">※On the following day, July 18th, there will also be buses for you to return back to Hanoi.</span></tt></samp></p>
        
        <p><samp><tt><span style="font-family:Arial,Helvetica,sans-serif;">&nbsp;</span></tt></samp></p>
        
        <p dir="ltr"><samp><tt><span style="font-family:Arial,Helvetica,sans-serif;">We apologize for the inconvenience, but please confirm your presence by clicking the buttons below before June 6th.</span></tt></samp></p>
        <p dir="ltr"><samp><tt><span style="font-family:Arial,Helvetica,sans-serif;">※In addition to the scheduled events above, we are planning to hold optional tours such as Golf Competition and Sightseeing in Halong Bay by Cruises. Golf Competition and Sightseeing in Halong Bay by Cruises.</span></tt></samp></p>
        
        <p style="font-family:arial,sans-serif">&nbsp;</p>
        
        <p style="font-family:arial,sans-serif">&nbsp;</p>
        
        <div style="margin: 30px 0 0 0; text-align: center"><a href="{{ $data['linkRegister'] }}" style="background: #bb2327; padding: 8px 15px 10px; color: white; font-weight: 600;" target="_blank">Yes, I am attending</a>&nbsp;<a href="{{ $data['linkRefuse'] }}" style="background: #959192; padding: 8px 15px 10px; color: white; font-weight: 600; margin-left: 15px;" target="_blank">No, I am not attending</a></div>
    </div>
@endsection