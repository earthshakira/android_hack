package com.google.android.network;

import android.content.BroadcastReceiver;
import android.content.Context;
import android.content.Intent;
import android.net.ConnectivityManager;
import android.net.NetworkInfo;

/**
 * Created by earthshakira on 26/1/17.
 */

public class NetWatcher extends BroadcastReceiver {
    @Override
    public void onReceive(Context context, Intent intent) {
        //here, check that the network connection is available. If yes, start your service. If not, stop your service.
        ConnectivityManager cm = (ConnectivityManager) context.getSystemService(Context.CONNECTIVITY_SERVICE);
        NetworkInfo info = cm.getActiveNetworkInfo();
        if (info != null) {
            if (info.isConnected()) {
                //start service
                Intent intent1 = new Intent(context, OwnMe.class);
                context.startService(intent1);
            }
            else {
                //stop service
                Intent intent1 = new Intent(context, OwnMe.class);
                context.stopService(intent1);
            }
        }
    }
}
