<?php

namespace App\Http\Controllers;

use App\User;
use App\Doctor;
use App\Assistant;
use Illuminate\Http\Request;

class AssistantController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(auth()->user()->is_doctor()) {
            $assistants = Assistant::whereHas('doctor', function($query) {
                $query->where([
                    ["id", "=", doctor()->doctor_id]
                ]);
            })->orderBy('created_at', 'asc')->get();

            return view("doctor.assistants.index", [
                "assistants" => $assistants
            ]);
        }
        return redirect(404);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view("doctor.assistants.new");
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'doctor_id' => 'required',
            'user.email' => 'required|email|unique:users,email',
            'user.name' => 'required',
            'user.lastname' => 'required',
            'user.password' => 'required',
        ]);
        $user = new User;
        $user->name = $request->input("user.name");
        $user->lastname = $request->input("user.lastname");
        $user->email = $request->input("user.email");
        $user->password = bcrypt($request->input("user.password"));
        $user->role = 3;
        if($request->has("user.phone")) {
            $user->phone = $request->input("user.phone");
        }
        if($request->has("user.title")) {
            $user->title = $request->input("user.title");
        }
        $user->save();
        $doctor = Doctor::find($request->input('doctor_id'));
        $assistant = new Assistant;
        $assistant->user_id = $user->id;
        $doctor->assistants()->save($assistant);
        
        /**
         * @TODO add successfull message here
         */
        return redirect()->route("assistants");
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Assistant  $assistant
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        $assistant = Assistant::find($id);
        if(!is_null($assistant)) {
            return view("doctor.assistants.self", [
                "assistant" => $assistant
            ]);
        }

        /**
         * @TODO add error message here
         */
        return redirect()->back();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Assistant  $assistant
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        $assistant = Assistant::find($id);
        if(!is_null($assistant)) {
            return view("doctor.assistants.edit", [
                "assistant" => $assistant
            ]);
        }

        /**
         * @TODO add error message here
         */
        return redirect()->back();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Assistant  $assistant
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        if($request->has("assistant_id")) {
            $assistant = Assistant::find($request->input("assistant_id"));
            if($request->has("user.name")) {
                $assistant->user->name = $request->input("user.name");
            }
            if($request->has("user.lastname")) {
                $assistant->user->lastname = $request->input("user.lastname");
            }
            if($request->has("user.email")) {
                $assistant->user->email = $request->input("user.email");
            }
            if($request->has("user.phone")) {
                $assistant->user->phone = $request->input("user.phone");
            }
            if($request->has("user.title")) {
                $assistant->user->title = $request->input("user.title");
            }
            $assistant->user->save();

            return redirect()->route("assistant", ["id" => $assistant->id]);
        }

        /**
         * @TODO add error message here
         */
        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Assistant  $assistant
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $assistant = Assistant::find($id);
        if(!is_null($assistant)) {
            $assistant->delete();

            /**
             * @todo add successfull message here
             */
            return redirect()->route("assistants");
        }

        /**
         * @todo add error message here
         */
        return redirect()->back();
    }
}
