import java.util.Scanner;

public class HW6 {
    public static void main(String[] args) {
        Scanner sc = new Scanner(System.in);
        System.out.print("Start: ");
        int start = sc.nextInt();
        System.out.print("Stop: ");
        int stop = sc.nextInt();

        for (int num = start; num <= stop; num++)
        {
            System.out.println("Times Table of " + num + ": ");
            for (int i = 1; i <= 10; i++)
            {
                System.out.println(num + " x " + i + " = " + (num*i));
            }
        }
    }
}
